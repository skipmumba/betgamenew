<?php
class getmatch extends CI_Controller 
{
	public function getlistMatch($d=0,$m=0,$y=0,$catInput=0)
	{
		if($d==0 and $m==0 and $y==0)
		{
			$dateNow = $this->getDate();
		}
		else 
		{
			$dateNow['day']= $d ;
			$dateNow['month']= $m;
			$dateNow['year']= $y;
		}
		
		$this->db->from('catgame');
		$this->db->order_by("order", "asc");
		if($catInput != 0)
		{

			$this->db->where('cat_id',$catInput);
		}
		$query = $this->db->get(); 
		$matchArray = array();
		$indexArray = 0;
		$notNullCat = false;
		foreach($query->result() as $row)
		{
			$notNullCat = false;
			$catName = $row->cat_name;
			$catId = $row->cat_id;
			$catOrder = $row->order;		
			$catImage = $row->cat_image;		
			$arrayDate = array($dateNow['day'],$dateNow['day']+1,$dateNow['day']+2,$dateNow['day']+3,$dateNow['day']+4,$dateNow['day']+5);
			$this->db->from('matchgame');
			$this->db->where_in('day',$arrayDate);
			$this->db->where('month',$dateNow['month']);
			$this->db->where('year',$dateNow['year']);
			$this->db->where('cat_id',$catId);
			$queryMatch = $this->db->get();
			$matchIn = 0;
			foreach($queryMatch->result() as $matchData)
			{
				if($query->num_rows() >= 0)
				{
					$notNullCat = true;
					$matchArray[$indexArray]['catName'] = $catName;
					$matchArray[$indexArray]['catImage'] = $catImage;
					$matchArray[$indexArray]['catID'] = $catId;
					$matchArray[$indexArray]['catOrder'] = $catOrder;		

					$matchArray[$indexArray]['matchDetail'][$matchIn]['matchID'] = $matchData->match_id;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['team1'] = $matchData->team_1;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['team2'] = $matchData->team_2;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['pic1'] = $matchData->team1pic;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['pic2'] = $matchData->team2pic;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['day'] = $matchData->day;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['month'] = $matchData->month;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['year'] = $matchData->year;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['price1'] = $matchData->team1price;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['price2'] = $matchData->team2price;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['winner'] = $matchData->winner;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['time'] = $matchData->time;

					if($matchData->statusgame == 0)
					{
						$matchArray[$indexArray]['matchDetail'][$matchIn]['statusgame'] = $this->statusGame($matchData->day,
							$matchData->month,$matchData->year,$matchData->time);
					}
					else 
					{
						$matchArray[$indexArray]['matchDetail'][$matchIn]['statusgame'] = 'แข่งจบแล้ว';
					}

					$calpercent = $this->findPercent($matchData->team1price,$matchData->team2price);
					$plusSum = $matchData->team1price+$matchData->team2price;
					if($matchData->team1price == 1 && $matchData->team2price == 1)
					{
						
						$percentA = 0;
						$percentB = 0;	
					}
					else 
					{
						$percentA = $calpercent[0];
						$percentB = $calpercent[1];					
					}
					$matchArray[$indexArray]['matchDetail'][$matchIn]['percentA'] = $percentA;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['percentB'] = $percentB;
					$calOdds=$this->findOdds($percentA,$percentB);
					$oddA = $matchData->team2price != 1?$calOdds[0]:1; // if no bet return odd 1
					$oddB = $matchData->team1price != 1?$calOdds[1]:1; // if no bet return odd 1
					$matchArray[$indexArray]['matchDetail'][$matchIn]['oddA'] = $oddA;
					$matchArray[$indexArray]['matchDetail'][$matchIn]['oddB'] = $oddB;

				}
				$matchIn++;
			}	
			if($notNullCat)
			{
				$indexArray++;
			}
		}
		// echo '<pre>';
		echo json_encode($matchArray);
		// print_r($matchArray);
	}

	function statusGame($d,$m,$y,$time='00:00',$status=0)
	{
		$checkMatch = false;
		$timeNow = $this->getDate();
		if($y == $timeNow['year'])
		{
			if($m == $timeNow['month'])
			{
				if($d == $timeNow['day'])
				{
					$checkMatch = true;
				}
			}
		}		
		if($checkMatch)
		{	
			if($timeNow['minute'] < 10)
			{
				$timeNow['minute'] = '0'.$timeNow['minute'];
			}
			$hourMin =  explode(':', $time);
			$hourMinNow = $timeNow['hour'].$timeNow['minute'];
			$hourInput = $hourMin[0].$hourMin[1];			
				if($hourMinNow >= $hourInput)
				{

					return 'แข่งแล้ว';
				}
				else 
				{
					return 'ยังไม่แข่ง';
				}
		}
		else 
		{
			if($timeNow['month'] == $m)
			{
				if($timeNow['day'] > $d)
				{
					return 'แข่งแล้ว';
				}
				else 
				{
					return 'ยังไม่แข่ง';
				}
			}
			else 
			{
				if($timeNow['month'] > $m)
				{
					return 'แข่งแล้ว';
				}
				else 
				{
					return 'ยังไม่แข่ง';
				}
			}
		}
	}

	public function findPercent($team1,$team2)
	{
		//assume
		// Team A = 5,284/11,333 = 0.466*100 = 46.6% round(47%)	
		// Team B = 6,049/11,333 = 0.533*100 = 53.3% round(53%)
		// Team A 47% Team B 53%	

		$sum = $team1+$team2;
		$percentA = round(($team1/$sum)*100);
		$percentB = round(($team2/$sum)*100);
		return array($percentA,$percentB);
	}

	public function findOdds($percentA,$percentB)
	{
		// assume
		// percent A 47
		// percent B 53
		// Team A = 53/47 1.12 floor(1.10) +1 = 2.10 = x2.10
		// Team B = 47/53 0.88 floor(0.80) +1 = 1.80 = x1.80
		if($percentA != 0)
		{
			$oddA =  $this->floorp(($percentB/$percentA)+1,2);
			if($oddA > 91)
			{
				$oddA = 92;
			}
		

		}
		else 
		{
			$oddA = 92;
		}
		if($percentB != 0)
		{
			$oddB =  $this->floorp(($percentA/$percentB)+1,2);
			if($oddB > 91)
			{
				$oddB = 92;
			}
	
		}
		else 
		{
			$oddB = 92;
		}
				
		return array($oddA,$oddB);	
	}

	function floorp($val, $precision)
	{
	    $mult = pow(10, $precision);
	    return floor($val * $mult) / $mult;
	}



	public function getDate()
	{
		$json = file_get_contents('https://script.google.com/macros/s/AKfycbyd5AcbAnWi2Yn0xhFRbyzS4qMq1VucMVgVvhul5XqS9HkAyJY/exec?tz=Asia/Bangkok');
		$obj = json_decode($json);
		$date = array('day'=>$obj->day,'month'=>$obj->month,'year'=>$obj->year,'hour'=>$obj->hours,'minute'=>$obj->minutes);
		return $date;
	}

	public function daySelected()
	{	
		// if  next year this function will be bugs
		// i had to create new function specific for 12-1 month
		$montharray = $this->month();
		$dateNow = $this->getDate();
		$day = $dateNow['day'];
		$month = $dateNow['month'];
		$year = $dateNow['year'];
		$dayOfmonth = $montharray[$month]['days'];
		$startMoreday = 1;
		$tabArray = array();
		$arrayStart = 0;
		for($i=1;$i<=5;$i++)
		{
			switch($i)
			{
				case 1:
					if($day == 2)
					{
						$dayCal = $montharray[$month-1]['days'];
						$monthCal = $montharray[$month-1]['number'];
						
					}
					else if($day == 1)
					{
						$dayCal = $montharray[$month-1]['days'];
						$monthCal = $montharray[$month-1]['number'];
						$dayCal -= 1;
					}
					else 
					{
						$monthCal = $month;
						$dayCal = $day - 2;
					}		
					$tabArray[$arrayStart]['day'] = $dayCal;
					$tabArray[$arrayStart]['month'] =$monthCal;
					$tabArray[$arrayStart]['year'] = $year;
					$arrayStart++;
					break;
				case 2:
					if(($day - 1) > 0)
					{
						$dayCal = $day - 1;
						$monthCal = $month;
					}
					else 
					{

						$monthCal = $montharray[$month-1]['number'];
						if($dayCal != $montharray[$month-1]['days'])
							$dayCal+=1;	
					}
					$tabArray[$arrayStart]['day'] = $dayCal;
					$tabArray[$arrayStart]['month'] = $monthCal;
					$tabArray[$arrayStart]['year'] = $year;
					$arrayStart++;
					break;
				case 3:
					$tabArray[$arrayStart]['day'] = $day;
					$tabArray[$arrayStart]['month'] = $month;
					$tabArray[$arrayStart]['year'] = $year;
					$arrayStart++;
					break;
				case 4:
					
					if(($day + 1) <= $dayOfmonth)
					{
						$dayCal = $day + 1;
						$monthCal = $month;
					}
					else 
					{
						$dayCal = $startMoreday;
						$monthCal = $month+1;
						$startMoreday++;
					}
					$tabArray[$arrayStart]['day'] = $dayCal;
					$tabArray[$arrayStart]['month'] = $monthCal;
					$tabArray[$arrayStart]['year'] = $year;
					$arrayStart++;
					break;
				case 5:				
					if(($day + 2) <= $dayOfmonth)
					{
						$monthCal = $month;
						$dayCal = $day + 2;
					}
					else 
					{
						$monthCal = $month+1;
						$dayCal = $startMoreday;
					}
					$tabArray[$arrayStart]['day'] = $dayCal;
					$tabArray[$arrayStart]['month'] = $monthCal;
					$tabArray[$arrayStart]['year'] = $year;
					$arrayStart++;
					break;
			}
			
		}
		echo json_encode($tabArray);
	}
	public function month()
	{
		$months = array(
		  '1' => array(
		    'name'   => 'January',
		    'short'  => 'Jan',
		    'number' => 1,
		    'days'  => 31
		  ),
		  '2' => array(
		    'name'   => 'February',
		    'short'  => 'Feb',
		    'number' => 2,
		    'days'   => 28
		  ),
		  '3' => array(
		    'name'   => 'March',
		    'short'  => 'Mar',
		    'number' => 3,
		    'days'   => 31
		  ),
		  '4' => array(
		    'name'   => 'April',
		    'short'  => 'Apr',
		    'number' => 4,
		    'days'   => 30
		  ),
		  '5' => array(
		    'name'   => 'May',
		    'short'  => 'May',
		    'number' => 5,
		    'days'   => 31
		  ),
		  '6' => array(
		    'name'   => 'June',
		    'short'  => 'Jun',
		    'number' => 6,
		    'days'   => 30
		  ),
		  '7' => array(
		    'name'   => 'July',
		    'short'  => 'Jul',
		    'number' => 7,
		    'days'   => 31
		  ),
		  '8' => array(
		    'name'   => 'August',
		    'short'  => 'Aug',
		    'number' => 8,
		    'days'   => 31
		  ),
		  '9' => array(
		    'name'   => 'September',
		    'short'  => 'Sep',
		    'number' => 9,
		    'days'   => 30
		  ),
		  '10' => array(
		    'name'   => 'October',
		    'short'  => 'Oct',
		    'number' => 10,
		    'days'   => 31
		  ),
		  '11' => array(
		    'name'   => 'November',
		    'short'  => 'Nov',
		    'number' => 11,
		    'days'   => 30
		  ),
		  '12' => array(
		    'name'   => 'December',
		    'short'  => 'Dec',
		    'number' => 12,
		    'days'   => 31
		  ),
		);
		return $months;
	}
    
}



