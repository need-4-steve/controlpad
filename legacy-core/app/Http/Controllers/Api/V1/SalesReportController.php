<?php

namespace App\Http\Controllers\Api\V1;

use Mail;
use File;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\SalesReportRepository;
use App\Services\PayMan\EwalletService;
use Controllers\ZipArchive;
use League\Csv\Writer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\Csv\CsvService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use Swift_SmtpTransport;
use Swift_Mailer;

class SalesReportController extends Controller
{
    public function __construct(
        AuthRepository $authRepo,
        OrderRepository $orderRepo,
        SalesReportRepository $salesReportRepo,
        EwalletService $ewalletService,
        CsvService $csvService,
        EmailService $emailService,
        TextService $textService
    ) {
        $this->authRepo = $authRepo;
        $this->orderRepo = $orderRepo;
        $this->reportRepo = $salesReportRepo;
        $this->ewalletService = $ewalletService;
        $this->csvService = $csvService;
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;

    }

    public function getTaxTotal()
    {
        $request = request()->all();
        $corporateTaxes = $this->reportRepo->getTaxTotal($request, 'corp');
        $repTaxes = $this->reportRepo->getTaxTotal($request, 'rep');
        $fbcTaxes = $this->reportRepo->getTaxTotal($request, 'fbc');
        return response()->json(['corporate_taxes' => $corporateTaxes, 'rep_taxes' => $repTaxes, 'fbc_taxes' => $fbcTaxes], HTTP_SUCCESS);
    }

    public function getCorpIndex($orderType = null)
    {
        $request = request()->all();
        $sales = $this->reportRepo->getUserIndex($request, null, $orderType);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getCustIndex($orderType = null)
    {
        $request = request()->all();
        $sales = $this->reportRepo->getCustReportIndex($request, null, $orderType);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getCorpTotal()
    {
        $request = request()->all();
        $retailSales = $this->reportRepo->getUserTotal($request, null, 'retail');
        $wholeslaeSales = $this->reportRepo->getUserTotal($request, null, 'wholesale');
        return response()->json(['retail_sales' => $retailSales, 'wholesale_sales' => $wholeslaeSales], HTTP_SUCCESS);
    }

    public function getRepIndex()
    {
        $request = request()->all();
        $sales = $this->reportRepo->getRepsIndex($request);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getRep($userId = null)
    {
        $request = request()->all();
        if (!$this->authRepo->isOwnerAdmin()) {
            $userId = $this->authRepo->getOwnerId();
        }
        $sales = $this->reportRepo->getUserIndex($request, $userId);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getRepTotal($userId = null)
    {
        $request = request()->all();
        $sales = $this->reportRepo->getRepTotal($request, $userId);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getRepTransferIndex()
    {
        $request = request()->all();
        $sales = $this->reportRepo->getUserIndex($request, null, 'rep-transfer');
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getRepTransferTotal()
    {
        $request = request()->all();
        $retailSales = $this->reportRepo->getUserTotal($request, null, 'rep-transfer');
        return response()->json(['rep_transfers' => $retailSales], HTTP_SUCCESS);
    }

    public function getFbcUser($userId = null)
    {
        $request = request()->all();
        if (!$this->authRepo->isOwnerAdmin()) {
            $userId = $this->authRepo->getOwnerId();
        }
        $users = $this->reportRepo->getFbcUser($request, $userId);
        return response()->json($users, HTTP_SUCCESS);
    }

    public function getFbcIndex()
    {
        $request = request()->all();
        $users = $this->reportRepo->getFbcIndex($request);
        return response()->json($users, HTTP_SUCCESS);
    }

    public function getFbcTotal($userId = null)
    {
        $request = request()->all();
        if (!$this->authRepo->isOwnerAdmin()) {
            $userId = $this->authRepo->getOwnerId();
        }
        $total = $this->reportRepo->getFbcTotal($request, $userId);
        return response()->json($total, HTTP_SUCCESS);
    }

    public function getAffiliateIndex()
    {
        $request = request()->all();
        $users = $this->reportRepo->getAffiliateIndex($request);
        return response()->json($users, HTTP_SUCCESS);
    }
    public function getAffiliate($userId = null)
    {
        $request = request()->all();
        if (!$this->authRepo->isOwnerAdmin()) {
            $userId = $this->authRepo->getOwnerId();
        }
        $sales = $this->reportRepo->getAffiliateUser($request, $userId);
        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getAffiliateTotal($userId = null)
    {
        $request = request()->all();
        if (!$this->authRepo->isOwnerAdmin()) {
            $userId = $this->authRepo->getOwnerId();
        }
        $total = $this->reportRepo->getAffiliateIndexTotal($request, $userId);
        return response()->json($total, HTTP_SUCCESS);
    }

	public function sendmailCsvCorpSales()
    {
        $request = request()->all();
        $fileName = 'corpSales';

        $salesHeader = [
            'receipt_id',
            'customer_first_name',
            'customer_last_name',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_price',
            'order_type',
            'created_at',
			'email'
        ];
        $sales = $this->reportRepo->getUserIndex($request, null, null, false);
        $csv = $this->createCSVforsendmail($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

	public function createCSVforsendmail($request, $fileName, $dataHeaders, $salesData)
    {
        $dirName  = $fileName.'/'.date('Y-m-d');
        $savedFileDir = storage_path().'/temp/'.$dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';

        $header = ['Content-Type' => 'application/octet-stream'];
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($dataHeaders);
        foreach ($salesData as $sale) {
            if ($sale->cash === 0) {
                $sale->cash = 'false';
            } else {
                $sale->cash = 'true';
            }
            $array = [];
            foreach ($dataHeaders as $data) {
                if ($data === 'order_type') {
                    $array[$data] = $sale->orderType->name;
                } else {
                    $array[$data] = data_get($sale, $data);
                }
            }
            $csv->insertOne($array);
        }

        Storage::disk('public')->put($dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $csv);

        $csvfile = $dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';



		 $backup = Mail::getSwiftMailer();

		$transport = Swift_SmtpTransport::newInstance('smtp.mailgun.org', 587, 'tls');
		$transport->setUsername('postmaster@mg.piphany.com');
		$transport->setPassword('63b25eedeec268aea763bc013a028067');


		$gmail = new Swift_Mailer($transport);

		Mail::setSwiftMailer($gmail);

		$contactName = "Ringbombparty-DEV";
		$contactEmail = "no-reply@ringbombparty.com";
		$contactMessage = "Please find in attachment of Corporate Sales Report file";
		$temail = $savedFileDir;

		$data = array('name'=>$contactName, 'email'=>$contactEmail, 'body'=>$contactMessage);
		Mail::send('emails.standard', $data, function($message) use ($contactEmail, $contactName,$temail)
		{
			$message->from($contactEmail, $contactName);
			$message->to(auth()->user()->email, 'myName')->subject('Corporate Sales Report');
			$message->attach($temail);
		});

		Mail::setSwiftMailer($backup);


        return [$savedFileDir, $fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $header];
    }
    public function downloadCsvCorpSales()
    {
        $request = request()->all();
        $fileName = 'corpSales';

        $salesHeader = [
            'receipt_id',
            'customer_first_name',
            'customer_last_name',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_price',
            'order_type',
            'created_at'
        ];
        $sales = $this->reportRepo->getUserIndex($request, null, null, false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvRepSalesTotal()
    {
        $request = request()->all();
        $fileName = 'repSales';

        $salesHeader = [
            'id',
            'first_name',
            'last_name',
            'retail_total'
        ];
        $sales = $this->reportRepo->getRepsIndex($request, false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvTaxSales()
    {
        $request = request()->all();
        $fileName = 'salesTax';

        $salesHeader = [
            'receipt_id',
            'customer_first_name',
            'customer_last_name',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_price',
            'order_type',
            'cash',
            'created_at'
            ];
        $sales = $this->reportRepo->getUserIndex($request, null, 'all', false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvFbcSales()
    {
        $request = request()->all();
        $fileName = 'fbcSales';

        $salesHeader = [
            'id',
            'first_name',
            'last_name',
            'fbc_total'
        ];
        $sales = $this->reportRepo->getFbcIndex($request);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }
    public function downloadCsvRepSalesOrder()
    {
        $request = request()->all();
        $fileName = 'repSalesOrder';

        $salesHeader = [
            'receipt_id',
            'owner_first_name',
            'owner_last_name',
            'customer_first_name',
            'customer_last_name',
            'subtotal_price',
            'total_shipping',
            'total_tax',
            'total_price',
            'order_type',
            'created_at'
        ];
        $sales = $this->reportRepo->getUserIndex($request, null, 'rep', false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvFbcSalesOrder()
    {
        $request = request()->all();
        $fileName = 'fbcSalesOrder';

        $salesHeader = [
            'receipt_id',
            'owner_name',
            'customer_last_name',
            'customer_first_name',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_price',
            'order_type',
            'created_at'
        ];
        $sales = $this->reportRepo->getFbcUser($request, null, false);
        foreach ($sales as $sale) {
            if (isset($sale->lines[0]->owner->first_name) && $sale->lines[0]->owner->last_name) {
                $sale->owner_name = $sale->lines[0]->owner->first_name . " " . $sale->lines[0]->owner->last_name;
            }
        }
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvAffiliateSalesTotal()
    {
        $request = request()->all();
        $fileName = 'affiliateSales';

        $salesHeader = [
            'id',
            'first_name',
            'last_name',
            'retail_total'
        ];
        $sales = $this->reportRepo->getAffiliateIndex($request, false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function downloadCsvAffiliateSalesOrder()
    {
        $request = request()->all();
        $fileName = 'affiliateSalesOrder.csv';


        $salesHeader = [
            'receipt_id',
            'owner_first_name',
            'owner_last_name',
            'customer_first_name',
            'customer_last_name',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_price',
            'order_type',
            'created_at'
        ];
        $sales = $this->reportRepo->getAffiliateUser($request, null, false);
        $csv = $this->createCSVDownload($request, $fileName, $salesHeader, $sales);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function createCSVDownload($request, $fileName, $dataHeaders, $salesData)
    {
        $dirName  = $fileName.'/'.date('Y-m-d');
        $savedFileDir = storage_path().'/temp/'.$dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';

        $header = ['Content-Type' => 'application/octet-stream'];
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($dataHeaders);
        foreach ($salesData as $sale) {
            if ($sale->cash === 0) {
                $sale->cash = 'false';
            } else {
                $sale->cash = 'true';
            }
            $array = [];
            foreach ($dataHeaders as $data) {
                if ($data === 'order_type') {
                    $array[$data] = $sale->orderType->name;
                } else {
                    $array[$data] = data_get($sale, $data);
                }
            }
            $csv->insertOne($array);
        }

        Storage::disk('public')->put($dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $csv);

        $csvfile = $dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';






        return [$savedFileDir, $fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $header];
    }



    public function taxOwedByUser()
    {
        return response()->json($this->ewalletService->taxOwedByUser(request()->all()));
    }

    public function downloadTaxesOwedReportCSV()
    {
        $request = request()->all();
        $fileName = 'SalesTaxOwedReport';


        $salesHeader = [
            'userId',
            'name',
            'taxOwed'

        ];
        $taxOwed = $this->ewalletService->taxOwedByUser(request()->all());
        $csv = $this->csvService->createCSVDownload($fileName, $salesHeader, $taxOwed['data']);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }
}
