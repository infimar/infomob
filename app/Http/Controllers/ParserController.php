<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Goutte\Client;
use DB;
use StdClass;
use File;
use App\Category;
use App\Organization;
use App\Branch;
use App\Phone;
use App\Social;

class ParserController extends Controller
{
	// protected $client;

	public function __construct()
	{
		header('Content-Type: text/html; charset=utf-8');
  	// $this->client = new Client();
	}

  public function parser($limit)
	{
		$cities = [
      "kyizyilorda" => 4,
      "taraz" => 6,
      "almatyi" => 3,
      "karaganda" => 5, 
      "astana" => 2,
      "ust-kamenogorsk" => 7, 
      "semey" => 8,
      "kokshetau" => 9,
      "kostanay" => 10,
      "aktyubinsk" => 11,
      "uralsk" => 12,
      "atyirau" => 13,
      "aktau" => 14,
      "shyimkent" => 1,
    ];

    

    return "OK";
	}

  private function decodeUnicode($str)
  {
    return json_decode('"' . str_replace('u', '\u', $str . '"'));
  }

	private function read()
	{
		// $data = File::get(public_path() . "/data/orgs.txt");
 		// dd(json_decode($data));
 		
    // $total = DB::table('urls')->count();
    // $parsed = DB::table('parsedobjects')->where("name", "!=", "")->count();

    // dd("Left: " . ($total - $parsed));

		// $total = DB::table('urls')->count();
		// $parsed = DB::table('urls')->where("parsed", 1)->count();
 	  // $objects = DB::table('parsedobjects')->count();
 		
 	  // die("Left: " . ($total - $parsed) . " - " . $objects);
	}

	private function categories()
	{
    return;

		$categories = Category::roots()->get();
		foreach ($categories as $key => $category) 
		{
			$children = $category->descendants()->limitDepth(1)->get();
			foreach ($children as $key => $child)
			{
				$child->parent_id = 1;
				$child->save();
			}
		}

		dd("DONE");
	}

	private function cities()
	{
		$cities = [
      "kyizyilorda" => 0,
      "taraz" => 0,
      "almatyi" => 0,
      "karaganda" => 0, 
      "astana" => 0,
      "ust-kamenogorsk" => 0, 
      "semey" => 0,
      "kokshetau" => 0,
      "kostanay" => 0,
      "aktyubinsk" => 0,
      "uralsk" => 0,
      "atyirau" => 0,
      "aktau" => 0,
      "shyimkent" => 0,
    ];

    $total = 0;
		$objects = DB::table('parsedobjects')->get(['url']);
		foreach ($objects as $key => $object) 
		{
			$field = json_decode($object->url);
			$city = $field->city;

			$cities[$city] += 1;
			$total += 1;
		}

		dd([
			'cities' => $cities,
			'total' => $total
		]);
	}

  private function sluggify($string, $gost = false)
  {
    if ($gost)
    {
      $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
          "Е"=>"E","е"=>"e","Ё"=>"E","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
          "Й"=>"I","й"=>"i","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n","О"=>"O","о"=>"o",
          "П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t","У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f",
          "Х"=>"Kh","х"=>"kh","Ц"=>"Tc","ц"=>"tc","Ч"=>"Ch","ч"=>"ch","Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch",
          "Ы"=>"Y","ы"=>"y","Э"=>"E","э"=>"e","Ю"=>"Iu","ю"=>"iu","Я"=>"Ia","я"=>"ia","ъ"=>"","ь"=>"");
    }
    else
    {
      $arStrES = array("ае","уе","ое","ые","ие","эе","яе","юе","ёе","ее","ье","ъе","ый","ий");
      $arStrOS = array("аё","уё","оё","ыё","иё","эё","яё","юё","ёё","её","ьё","ъё","ый","ий");        
      $arStrRS = array("а$","у$","о$","ы$","и$","э$","я$","ю$","ё$","е$","ь$","ъ$","@","@");
                  
      $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
          "Е"=>"Ye","е"=>"e","Ё"=>"Ye","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
          "Й"=>"Y","й"=>"y","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n",
          "О"=>"O","о"=>"o","П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t",
          "У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f","Х"=>"Kh","х"=>"kh","Ц"=>"Ts","ц"=>"ts","Ч"=>"Ch","ч"=>"ch",
          "Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch","Ъ"=>"","ъ"=>"","Ы"=>"Y","ы"=>"y","Ь"=>"","ь"=>"",
          "Э"=>"E","э"=>"e","Ю"=>"Yu","ю"=>"yu","Я"=>"Ya","я"=>"ya","@"=>"y","$"=>"ye");
              
      $string = str_replace($arStrES, $arStrRS, $string);
      $string = str_replace($arStrOS, $arStrRS, $string);
    }
    
    $translated = iconv("UTF-8","UTF-8//IGNORE", strtr($string,$replace));
    $translated = strtolower($translated);
    $translated = str_replace(" ", "-", $translated);

    return $translated;
  }
}
