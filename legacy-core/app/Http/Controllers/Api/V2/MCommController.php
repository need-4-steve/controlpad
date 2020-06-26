<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Http\Controllers\Api\V2\MCommServices\MCommCommissionService;
use App\Http\Controllers\Api\V1\CommissionEngineController;
use App\Http\Controllers\Api\V2\MCommHelpers\Dispatcher;
use App\Http\Controllers\Api\V2\MCommHelpers\MemoryCache;
use Carbon\Carbon;

class MCommController extends CommissionEngineController
{
	const MCOMM_BASEURL='https://multicomapi.com/';
	const REPORT_REQUEST_HEADERS = ['token'=>"c3fe54104a444b07817286da8cac2def", 'Content-Type' => 'application/x-www-form-urlencoded'];
	const USER_AND_ORDER_REQUEST_HEADERS = ['token'=>"7f367ab97eac4872b1e2e78bd2725e8b", 'Content-Type' => 'application/json'];
	protected $api;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct(
        MCommCommissionService $commissionService,
        OrderRepository $orderRepo,
        UserRepository $userRepo
    ) {
		parent::__construct(...func_get_args());
	}

	/**
	 * __get
	 *
	 * @param  mixed $key
	 *
	 * @return void
	 */
	public function __get( $key )
    {
        return $$key;
    }

    /**
     * addUser
     *
     * @param  mixed $userId
     *
     * @return void
     */
    public function addUser($userId=null)
    {
		return $this->commissionService->addUser(User::where('id', (is_null($userId) ? \Auth::user()->id : $userId))->withTrashed()->first());
	}

	/**
	 * findUser
	 *
	 * @param  mixed $user
	 *
	 * @return void
	 */
	public function findUser($userId=null){
		return $this->commissionService->findUser(User::where('id', (is_null($userId) ? \Auth::user()->id : $userId))->withTrashed()->first());
	}

	/**
	 * postOrder
	 *
	 * @return void
	 */
	public function postOrder($orderId){
		return response()->json($this->commissionService->addReceipt(Order::where('id',$orderId)->first()));
	}

	public static function getMemCacheKey(){
		return ftok(__FILE__,"1");
	}

	/**
	 * findPeriod
	 *
	 * @param  mixed $date
	 *
	 * @return void
	 */
	public function findPeriod($date){
		$testDate = date('F Y',strtotime($date));
		$this->api = new Dispatcher();
		$requestBody=['template' => 'mca_Periods','requestinfo' => 'true','onlycount' => 'false','skip' => '0','take' => '50','key' => '878978978978','status' => '-1'];
		$data = $this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',array_merge(self::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody);
		$periodFound=null;
		cache(['mcomm_all_periods'=>$data['data']],$this->getEndOfMonthTime());
		foreach ($data['data'] as $period){
			if (trim(strtolower($period['p_periodtxt']))===strtolower($testDate)) {
				$periodFound=$period;
				break;
			}
		}
		return response()->json(is_null($periodFound) ? ['error'=>'No Date Found'] : $periodFound, HTTP_SUCCESS);
	}

	public function getGenealogyReport($userId=null){
		$userId=is_null($userId) ? \Auth::user()->id : $userId;
		if ($returnData=cache("genealogy-$userId")){
			return response()->json($returnData);
		}
        $this->api = new Dispatcher();
		$requestBody=[
			'template' 		=> 	'mca_Genealogy',
			'requestinfo'	=> 	'true',
			'onlycount' 	=> 	'false',
			'skip' 			=> 	'0',
			'take' 			=> 	'50',
			'key' 			=> 	'878978978978',
			'companyaid' 	=>	$userId==1 ? 813322 : $userId, //test values
		];
		$levelRecords=$this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',array_merge(self::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody)['data'];
		$currentPeriod=$this->getCurrentPeriod(true);
		$returnData=[];
		foreach ($levelRecords as $levelMember){
			if ($levelMember['level'] > 0){
				$this->api=new Dispatcher();
				$memberData=$this->getMemberPeriodKPIdata($levelMember['companyaid'],$currentPeriod,true);
				$memberData['name']=$levelMember['name'];
				$memberData['advisor_name']=$levelMember['enrollername'];
				$memberData['level']=$levelMember['level'];
				$memberData['companyaid']=$levelMember['companyaid'];
				$moreMemberData=$this->getMemberData($levelMember['companyaid'],true);
				$memberData['enrollment_date']=$moreMemberData['signupdate'];
				$memberData['websitename']=$moreMemberData['websitename'];
				$memberData['email']=$moreMemberData['email'];
				$memberData['associatetypedesc']=$moreMemberData['associatetypedesc'];
				$memberData['statusdesc']=$moreMemberData['statusdesc'];
				$memberData['associatetype']=$moreMemberData['statusdesc'];
				$memberData['custom1']=$moreMemberData['custom1'];//phonenumber
				$returnData[]=$memberData;
			}
		}
		cache(["genealogy-$userId" => $returnData],Carbon::now()->addMinutes(30));
		return response()->json($returnData);
		/*{
            "recordid": 0,
            "companyaid": "813322",
            "name": "Patti  Copus",
            "level": 0,
            "companyenrollerid": "813315",
            "enrollername": "Judy  Hughes"
        },
        {
            "recordid": 1,
            "companyaid": "813383",
            "name": "Meagan  Mitchell",
            "level": 1,
            "companyenrollerid": "813322",
            "enrollername": "Patti  Copus"
		},
		{...},
		*/
	}
	
	public function getMemberData($userId=null,$returnLocally=false){
		$userId=is_null($userId) ? \Auth::user()->id : $userId;
		if ($userId>-1) {
			if ($returnData=cache("member-$userId")){
				return $returnData;
			}
		}
        $this->api = new Dispatcher();
		$requestBody=[
			'template' 		=> 	'mca_Members',
			'requestinfo'	=> 	'true',
			'onlycount' 	=> 	'false',
			'skip' 			=> 	'0',
			'take' 			=> 	'50',
			'key' 			=> 	'878978978978',
			'companyaid' 	=>	$userId==1 ? 813322 : $userId, //test values
		];
		$returnData=$this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',array_merge(self::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody)['data'];
		if ($userId==-1){
			foreach ($returnData as $singleMember)
				cache(["member-".$singleMember['companyaid']=>$singleMember],Carbon::now()->addMinutes(60));
		} else {
			cache(["member-$userId"=>$returnData[0]],Carbon::now()->addMinutes(60));
		}
		if ($returnLocally) return $returnData[0];
		return $userId > -1 ? response()->json($returnData[0],HTTP_SUCCESS) : response()->json($returnData,HTTP_SUCCESS);
		/*{
            "associateid": 813322,
            "companyaid": "813322",
            "firstname": "Patti",
            "lastname": "Copus",
            "associatetype": 2,
            "associatetypedesc": "Distributor",
            "signupdate": "2019-03-30 00:00:00.0",
            "recognitionname": "",
            "websitename": "",
            "email": "maggielouie0914@gmail.com",
            "country": "US",
            "status": 1,
            "statusdesc": "Active",
            "enrollerid": 813315,
            "companyenrollerid": "813315",
            "enrollername": "Judy  Hughes",
            "placementid": "",
            "companyplacementid": "",
            "placementname": "",
            "side": "",
            "custom1": "",
            "custom2": "",
            "custom3": "",
            "custom4": "",
            "custom5": ""
        }*/
	}

	private function getEndOfMonthTime(){
		$end = strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00')) - 1;
		$now = time();
		$numSecondsUntilEnd = $end - $now;
		return $numSecondsUntilEnd / 60;
	}

	/**
	 * getCurrentPeriod
	 *
	 * @param  mixed $returnLocally
	 *
	 * @return void
	 */
	public function getCurrentPeriod($returnLocally=false){
		if (!empty($periodData=cache('mcomm_period'))){
			return $returnLocally ? $periodData['period'] : response()->json($periodData, HTTP_SUCCESS);
		} else {
			$this->api = new Dispatcher();
			$requestBody=['template' => 'mca_Periods','requestinfo' => 'true','onlycount' => 'false','skip' => '0','take' => '50','key' => '878978978978','status' => '2'];
			$data = $this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',array_merge(self::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody);
			$periodName=$data['data'][0]['p_periodtxt'];
			$period = $data['data'][0]['period'];
			if ($timeTillForget=(strtotime($data['data'][0]['finish']) - time()) > 60) cache(['mcomm_period'=> $data['data'][0]], $timeTillForget);
			if ($returnLocally) return ($period-1); // -1 for testing
			return response()->json($data['data'][0], HTTP_SUCCESS);
		}
	}

	/**
	 * getKPIdata
	 *
	 * @param  mixed $userId
	 * @param  mixed $specificPeriod
	 *
	 * @return void
	 */
	private function getKPIdata($userId,$specificPeriod=null){
		// cache()->flush();
		$period=!is_null($specificPeriod) ? $specificPeriod : $this->getCurrentPeriod(true);
		if ($period<$this->getCurrentPeriod(true)){
			if ($historicalData=cache('historical_kpis')){
				if ($period==-1){
					if ($currentPeriodData=cache('current_kpis')){
						if (!empty($currentPeriodData['rankData'])){
							array_push($historicalData,$currentPeriodData);
							return $historicalData;
						}
					}
					$period=$this->getCurrentPeriod(true);
				} else {
					return $historicalData[$period];
				}
			}
		} else {
			if ($currentPeriodData=cache('current_kpis')){
				return $currentPeriodData;
			}
		}
		if ($userId <= 0) $userId=-1;
		$this->api = new Dispatcher();
		$requestBody=[
			'template' 		=> 	'mca_QualHistory',
			'requestinfo'	=> 	'true',
			'onlycount' 	=> 	'false',
			'skip' 			=> 	'0',
			'take' 			=> 	'50',
			'key' 			=> 	'878978978978',
			'period' 		=>	$period,
			// 'period' 		=> 	!is_null($specificPeriod) ? $specificPeriod : $period>50000 ? 50000 : $period, //test values
			'companyaid' 	=>	$userId==1 ? 813322 : $userId, //test values
		];
		$rankData = $this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',self::REPORT_REQUEST_HEADERS,$requestBody);
		$rankData = empty($rankData['data']) ? 
			null : ($period==-1 ? $rankData['data'] : $rankData['data'][0]);
		$requestBody['template']='mca_CommissionDetail';
		$commissionData = $this->api->request('POST', self::MCOMM_BASEURL.'/gr/data/',self::REPORT_REQUEST_HEADERS,$requestBody);
		$commissionData = empty($commissionData['data']) ? 
			null : $this->processCommissionData($commissionData['data'], $period==-1 );
		if ($period==-1) {
			$newRankArray=[];$newCommissionArray=[];
			foreach ($rankData as $periodRank)
				$newRankArray[$periodRank['period']] = $periodRank;
			foreach ($commissionData as $commissionPeriod=>$periodCommission)
				$newCommissionArray[$commissionPeriod] = $periodCommission;
			$allData= $this->merge_arrays($newRankArray,$newCommissionArray);
			if (array_key_exists($this->getCurrentPeriod(true),$allData)){
				// cache(['current_kpis' => $allData[$this->getCurrentPeriod(true)]] ,5);
				array_pop($allData);
			}
			cache(['historical_kpis'=>$allData],$this->getEndOfMonthTime());
			return $allData;
		} else {
			// cache(['current_kpis'=>["user"=>$userId,"period"=>['period'=>$period,'periodName'=>$rankData['periodtxt']],"rankData"=>$rankData,"commissionData"=>$commissionData]],5);
			if ($specificPeriod==-1){
				array_push($historicalData, ["user"=>$userId,"period"=>['period'=>$period,'periodName'=>$rankData['periodtxt']],"rankData"=>$rankData,"commissionData"=>$commissionData]);
				return $historicalData;
			}
		}
		return ["user"=>$userId,"period"=>['period'=>$period,'periodName'=>$rankData['periodtxt']],"rankData"=>$rankData,"commissionData"=>$commissionData];
	}

	public function getActivePeriods($userId=null){
		if ($data=cache('active-periods-'.$userId)) return $data;
		$userId= is_null($userId) ? \Auth::user()->id : $userId;
		$periodsData=$this->getKPIdata($userId,-1);
		$periods=[];
		foreach($periodsData as $periodData){
			$periods[]=['period'=>$periodData['period'],'periodName'=>$periodData['periodtxt']];
		}
		cache(['active-periods-'.$userId => $periods],$this->getEndOfMonthTime());
		return response()->json($periods);
	}

	/**
	 * merge_arrays
	 *
	 * @param  mixed $arr1
	 * @param  mixed $arr2
	 *
	 * @return void
	 */
	private function merge_arrays($arr1,$arr2){
		//because array_merge_recursive wasn't working properly
		$keys=array_keys($arr1);
		$merged=[];
		// $merged=$this->array_slice_assoc($arr2,$keys);
		foreach ($arr1 as $key=>$val){
			$merged[$key]=$arr1[$key];
			if (array_key_exists($key,$arr2) && is_array($arr2)) {
				foreach ($arr2[$key] as $name=>$value){
					$merged[$key][$name]=$value;
				}
				$this->array_slice_assoc($arr2,[$key]);
				if(count($arr2)<=0) break;
			}
		}
		return $merged;
	}

	/**
	 * array_slice_assoc
	 *
	 * @param  mixed $array
	 * @param  mixed $keys
	 *
	 * @return void
	 */
	private function array_slice_assoc($array,$keys) {
		return array_intersect_key($array,array_flip($keys));
	}
	
	/**
	 * processCommissionData
	 *
	 * @param  mixed $commissionData
	 * @param  mixed $allTime
	 *
	 * @return void
	 */
	private function processCommissionData($commissionData, $allTime=false){
		if (empty($commissionData) || count($commissionData)<1) return false;
		$levelVolumes=[];
		if ($allTime){
			$tmpData=[];
			foreach($commissionData as $cd) $tmpData[$cd['period']][]=$cd;
			$commissionData=$tmpData;
			$tmpData=[];
			foreach ($commissionData as $period=>$periodCommissionData) {
				for ($i=1;$i<=6;$i++) 
					$levelVolumes[$i]=$this->combinedSum($periodCommissionData,"Residual","amount",true,$i);
				$tmpData[$period]=[
					'commissionableRetailVolume'=>	$this->combinedSum($periodCommissionData,"Retail","baseamt"),
					'residualBonus'				=>	$this->combinedSum($periodCommissionData,"Residual","amount"),
					'fastStartCommissions'		=>	$this->combinedSum($periodCommissionData,"Fast Cash","amount",false),
					'retailBonus'				=>	$this->combinedSum($periodCommissionData,"Retail","amount"),
					'levelVolumes'				=>	$levelVolumes
				];
			}
			return $tmpData;
		} else {
			for ($i=1;$i<=6;$i++) 
				$levelVolumes[$i]=$this->combinedSum($commissionData,"Residual","amount",true,$i);
			return [
				'commissionableRetailVolume'=>	$this->combinedSum($commissionData,"Retail","baseamt"),
				'residualBonus'				=>	$this->combinedSum($commissionData,"Residual","amount"),
				'fastStartCommissions'		=>	$this->combinedSum($commissionData,"Fast Cash","amount",false),
				'retailBonus'				=>	$this->combinedSum($commissionData,"Retail","amount"),
				'levelVolumes'				=>	$levelVolumes
			];
		}
		
	}

	private function combinedSum($array,$commissionType,$amountColumn, $textEquals=true, $level=null){
		return array_sum(
			array_column(
				array_filter($array, function ($row) use ($level,$textEquals,$commissionType,$amountColumn) { return !is_null($level) ? $row['lvl']==$level ? $textEquals ? $row['commissiontypetxt']===$commissionType : strpos($row['commissiontypetxt'],$commissionType)!==false : false : $textEquals ? trim($row['commissiontypetxt'])==$commissionType : strpos($row['commissiontypetxt'],$commissionType);})
			,$amountColumn)
		);
	}

	public function getMemberPeriodKPIdata($userId=null, $period=null, $localCall=null){
		$result = $this->getKPIdata(is_null($userId) ? \Auth::user()->id : $userId,$period);
		if (!is_null($period)){
			if ($period==-1){
				$response=[];
				$relevantKeys=['pin rank','new rank','periodtxt','pv','customers count','tgv','commissionableRetailVolume','residualBonus','fastStartCommissions','retailBonus','levelVolumes'];
				var_dump($result);
				foreach ($result as $period=>$periodData){
					foreach ($relevantKeys as $testKey)
						if (empty($periodData[$testKey])) $periodData[$testKey]=0;
					$response[$period]=[
						'user'							=>	is_null($userId) ? \Auth::user()->id : $userId,
						'period'						=>	$period,
						'periodName'					=>	$periodData['periodtxt'],
						'rank'							=>	$periodData['new rank'] > $periodData['pin rank'] ? $periodData['newrankdescr'] : $periodData['pinrankdescr'],
						'personalVolume' 				=>	$periodData['pv'],
						'customerCount'					=>	$periodData['customers count'],
						'careerTitle'					=>	$periodData['pinrankdescr'],
						'teamGroupVolume'				=>	$periodData['tgv'],
						'commissionableRetailVolume'	=>	$periodData['commissionableRetailVolume'],
						'residualBonus'					=>	$periodData['residualBonus'],
						'fastStartCommissions'			=>	$periodData['fastStartCommissions'],
						'retailBonus'					=>	$periodData['retailBonus'],
						'levelVolumes'					=>	is_array($periodData['levelVolumes']) ? $periodData['levelVolumes'] : [1=>0,2=>0,3=>0,4=>0,5=>0,6=>0],	
					];
				}
				return response()->json($response,HTTP_SUCCESS);
			}
		}
		if ($localCall) {
			return [
				'user'							=>	is_null($userId) ? \Auth::user()->id : $userId,
				'period'						=>	$result['period']['period'],
				'periodName'					=>	$result['period']['periodName'],
				'rank'							=>	!empty($result["rankData"]) ? $result["rankData"]['new rank'] > $result["rankData"]['pin rank'] ? $result["rankData"]['newrankdescr'] : $result["rankData"]['pinrankdescr'] : "N/A",
				'personalVolume' 				=>	!empty($result["rankData"]) ? $result["rankData"]['pv'] : 0,
				'customerCount'					=>	!empty($result["rankData"]) ? $result["rankData"]['customers count'] : 0,
				'careerTitle'					=>	!empty($result["rankData"]) ? $result["rankData"]['pinrankdescr'] : 'N/A',
				'teamGroupVolume'				=>	!empty($result["rankData"]) ? $result["rankData"]['tgv'] : 0,
				'commissionableRetailVolume'	=>	is_array($result["commissionData"]) ? $result["commissionData"]['commissionableRetailVolume']:0,
				'residualBonus'					=>	is_array($result["commissionData"]) ? $result["commissionData"]['residualBonus']:0,
				'fastStartCommissions'			=>	is_array($result["commissionData"]) ? $result["commissionData"]['fastStartCommissions']:0,
				'retailBonus'					=>	is_array($result["commissionData"]) ? $result["commissionData"]['retailBonus']:0,
				'levelVolumes'					=>	is_array($result["commissionData"]) ? $result["commissionData"]['levelVolumes']:[1=>0,2=>0,3=>0,4=>0,5=>0,6=>0],
			];
		}
		return response()->json([
			'user'							=>	is_null($userId) ? \Auth::user()->id : $userId,
			'period'						=>	$result['period']['period'],
			'periodName'					=>	$result['period']['periodName'],
			'rank'							=>	!empty($result["rankData"]) ? $result["rankData"]['new rank'] > $result["rankData"]['pin rank'] ? $result["rankData"]['newrankdescr'] : $result["rankData"]['pinrankdescr'] : "N/A",
			'personalVolume' 				=>	!empty($result["rankData"]) ? $result["rankData"]['pv'] : 0,
			'customerCount'					=>	!empty($result["rankData"]) ? $result["rankData"]['customers count'] : 0,
			'careerTitle'					=>	!empty($result["rankData"]) ? $result["rankData"]['pinrankdescr'] : 'N/A',
			'teamGroupVolume'				=>	!empty($result["rankData"]) ? $result["rankData"]['tgv'] : 0,
			'commissionableRetailVolume'	=>	!empty($result["commissionData"]) ? $result["commissionData"]['commissionableRetailVolume']:0,
			'residualBonus'					=>	!empty($result["commissionData"]) ? $result["commissionData"]['residualBonus']:0,
			'fastStartCommissions'			=>	!empty($result["commissionData"]) ? $result["commissionData"]['fastStartCommissions']:0,
			'retailBonus'					=>	!empty($result["commissionData"]) ? $result["commissionData"]['retailBonus']:0,
			'levelVolumes'					=>	!empty($result["commissionData"]) ? $result["commissionData"]['levelVolumes']:[1=>0,2=>0,3=>0,4=>0,5=>0,6=>0],
		],200);
	}
	
}