<?php

namespace App\Services\Csv;

use Mail;
use League\Csv\Writer;
use Illuminate\Support\Facades\Storage;

class CsvService
{
    public function __construct()
    {
    }

    public function createCSVDownload($fileName, $headersData, $requestData)
    {
        $dirName  = $fileName.'/'.date('Y-m-d');
        $savedFileDir = storage_path().'/temp/'.$dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';
        $header = ['Content-Type' => 'application/octet-stream'];
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($headersData);
        foreach ($requestData as $data) {
            $array = [];
            foreach ($headersData as $headers) {
                  $array[$headers] = data_get($data, $headers);
            }
            $csv->insertOne($array);
        }

        Storage::disk('public')->put($dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $csv);
        return [$savedFileDir, $fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $header];
    }


	public function createCSVsendasemail($fileName, $headersData, $requestData)
    {
        $dirName  = $fileName.'/'.date('Y-m-d');
        $savedFileDir = storage_path().'/temp/'.$dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';
        $header = ['Content-Type' => 'application/octet-stream'];
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($headersData);
        foreach ($requestData as $data) {
            $array = [];
            foreach ($headersData as $headers) {
                  $array[$headers] = data_get($data, $headers);
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
		$contactMessage = "Please find in attachment of Subscription Report file";
		$temail = $savedFileDir;
		
		$data = array('name'=>$contactName, 'email'=>$contactEmail, 'body'=>$contactMessage);
		Mail::send('emails.standard', $data, function($message) use ($contactEmail, $contactName,$temail)
		{   
			$message->from($contactEmail, $contactName);
			$message->to(auth()->user()->email, 'myName')->subject('Subscription Report');
			$message->attach($temail);
		});

		Mail::setSwiftMailer($backup);
	  
        return [$savedFileDir, $fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $header];
    }
}
