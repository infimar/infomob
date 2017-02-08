<?php

namespace App\Console\Commands;

use App\Branch;
use App\Category;
use App\Organization;
use App\Phone;
use App\Social;
use DB;
use File;
use Goutte\Client;
use Illuminate\Console\Command;
use StdClass;
use XmlParser;

class ParseIt extends Command
{
  // protected $hash = "ff4f6fd6ff0dd89a"; // karagandy
  protected $hash = "60ff6b2e619458b4"; // ustkaman

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'parser:do {city} {start} {end}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();

      $this->client = new Client();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $time_start = microtime(true);
    // $this->getFromGis("rubrics-karagandy.js");
      // $this->seedGis();


    // $this->getOrgIds();
    // $this->getBranches();

    // $this->parseOrgPages();
    //$this->parseShymkent();
    
    $arguments = $this->argument();
    // dd($arguments);

    // $this->prepareOrgIds(public_path("/data/cities/uralsk"));
    $this->prepareBranchIds(public_path("/data/cities/almaty/branches"));
    //$this->getBranchesFromGis(public_path("/data/orgs/" . $arguments['city'] . ".txt"), intval($arguments['start']), intval($arguments['end']));

    $time_end = microtime(true);
    $this->info("Done in " . ($time_end - $time_start) . " seconds");
  }

  public function prepareOrgIds($directory) {
    $orgs = [];
    $filename = public_path("/data/orgs.txt");

    // get all files
    $files = File::allFiles($directory);
    foreach ($files as $file)
    {
      $contents = File::get($file);
      $data = json_decode($contents);

      foreach ($data->result->items as $item)
      {
        $orgs[$item->org->id] = strval($item->org->id);
      }
    }

    foreach ($orgs as $key => $value)
    {
      $bytesWritten = File::append($filename, $value . "\n");

      if ($bytesWritten === false)
      {
        die("Couldn't write to the file.");
      }
    }
  }

  public function prepareBranchIds($directory) {
    $orgs = [];
    $filename = public_path("/data/ids.txt");

    // get all files
    $files = File::allFiles($directory);
    foreach ($files as $file)
    {
      $contents = File::get($file);
      $data = json_decode($contents);

      foreach ($data->result->items as $item)
      {
        $orgs[$item->id] = strval($item->id);
      }
    }

    foreach ($orgs as $key => $value)
    {
      $bytesWritten = File::append($filename, $value . "\n");

      if ($bytesWritten === false)
      {
        die("Couldn't write to the file.");
      }
    }
  }

  public function getBranchesFromGis($orgsFile, $start, $end) 
  {
    $contents = file($orgsFile);

    foreach ($contents as $key => $line) {
      if ($key >= $start && $key <= $end) {
        $this->getOrgData(trim($line), $key, count($contents));
      }
    }
  }

  public function getOrgData($orgId, $num, $countOrgs) {
    $proxy = $this->getRandomProxy();
    // dd($proxy);

    $opts = array(
      'https'=>array(
          'method'=>"GET",
          'proxy' => $proxy,
      ),
      "ssl" => array(
          "verify_peer"=>false,
          "verify_peer_name"=>false,
      )
    );
    $context = stream_context_create($opts);

    $data = file_get_contents("https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=1&page_size=12&org_id=" . $orgId . "&hash=" . $this->hash . "&stat%5Bpr%5D=8&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Chash%2Csearch_attributes&key=ruczoy1743", false, $context);

    $data = json_decode($data);

    if ($data->meta->code == 404) 
    {
      $this->info("no results for " . $orgId);
      continue;
    }

    $total = $data->result->total;
    $numOfPages = intval(ceil($total / 50.0));

    if ($numOfPages == 1)
    {
      $this->downloadBranchItems($orgId, 1);
    }
    else
    {
      for ($i = 1; $i <= $numOfPages; $i++) 
      {
        $this->downloadBranchItems($orgId, $i);
      }
    }

    $this->info('Org: ' . $orgId . ' done. Left: ' . ($countOrgs - $num - 1));
  }

  public function downloadBranchItems($orgId, $pageNum) {
    $proxy = $this->getRandomProxy();

    $path = public_path() . "/data/branches/";
    $url = "https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=1&page_size=12&org_id=" . $orgId . "&hash=" . $this->hash . "&stat%5Bpr%5D=8&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Chash%2Csearch_attributes&key=ruczoy1743";

    $opts = array(
      'https' => array(
        'method'=>"GET",
        'proxy' => $proxy,
      ),
      "ssl" => array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      )
    );
    $context = stream_context_create($opts);

    $data = file_get_contents($url, false, $context);
    file_put_contents($path . $orgId . "-" . $pageNum . ".json", $data);
  }

  public function crawl($url)
  {
    $crawler = $this->client->request('GET', $url);
    
    $indicatorKey = "Подробнее";
    $result = [];
    $idx = 0;
    $skip = false;
    $item = [];

    $categories = [];

    $data = $crawler->filterXPath('//table[@cellpadding="2"]/tr');
    $data->each(function ($node, $i) use (&$result, $indicatorKey, &$idx, $skip, &$item, &$categories) 
    {
      if ($i < 5) return;

      $text = $node->text();
      //echo $text . "<br>";

      if (mb_strpos($text, $indicatorKey) !== false) 
      {
        return;
      } 
      elseif (empty($text)) 
      {
        $result[$idx] = $item;

        $item = [];
        $idx += 1;
      }
      elseif (mb_strpos($text, "Категория") !== false) 
      {
        $item["category"] = $text;
      }
      elseif (mb_strpos($text, "Продукты") !== false) 
      {
        $item["description"] = $text;
      }
      elseif (mb_strpos($text, "Адрес") !== false) 
      {
        $item["address"] = $text;
      }
      elseif (mb_strpos($text, "Телефон") !== false) 
      {
        $item["phone"] = $text;
      }
      elseif (mb_strpos($text, "Факс") !== false) 
      {
        $item["fax"] = $text;
      }
      else 
      {
        $item["name"] = $text;
      }
    });

    // dd($result);

    $db = [];
    $i = 0;

    foreach ($result as $item) 
    {
      foreach ($item as $key => $value) 
      {
        if ($key == "name")
        {
          $value = str_replace(["\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, ".");
          $name = mb_substr(htmlspecialchars_decode($value), $start + 1);
          $db[$i]["name"] = trim($name);
        }
        elseif ($key == "description")
        {
          $start = mb_strpos($value, ":");
          $description = mb_substr(htmlspecialchars_decode($value), $start + 1);
          $db[$i]["description"] = trim($description);
        }
        elseif ($key == "category")
        {
          $value = str_replace(['.', "\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, '/');
          $category = mb_substr($value, $start + 1);          
          $db[$i]["category"] = trim($category);
        }
        elseif ($key == "address")
        {
          $value = str_replace(["\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, ':');
          $address = mb_substr($value, $start + 1);          
          $db[$i]["address"] = trim($address);
        }
        elseif ($key == "phone")
        {
          $start = mb_strpos($value, ':');
          $phone = mb_substr($value, $start + 1);

          // parse phone
          $phone = trim($phone);
          $start = mb_strpos($phone, "(");
          $codeCountry = mb_substr($phone, 0, $start);

          $end = mb_strpos($phone, ")");
          $codeOperator = mb_substr($phone, $start + 1, $end - $start - 1);

          $number = mb_substr($phone, $end + 1);
          $number = str_replace(["-", " "], "", $number);

          // if number contains ',' -> 2 numbers
          if (mb_strpos($number, ","))
          {
            $numbers = explode(",", $number);

            foreach ($numbers as $key => $num)
            {
              $db[$i]["phones"][] = [
                "type" => "work",
                "code_country" => trim($codeCountry),
                "code_operator" => trim($codeOperator),
                "number" => trim($num)
              ];
            }
          }
          else
          {
            $db[$i]["phones"][] = [
              "type" => "work",
              "code_country" => trim($codeCountry),
              "code_operator" => trim($codeOperator),
              "number" => trim($number)
            ];
          }
        }
        elseif ($key == "fax")
        {
          $start = mb_strpos($value, ':');
          $fax = mb_substr($value, $start + 1);

          // parse fax
          $fax = trim($fax);
          $start = mb_strpos($fax, "(");
          $codeCountry = mb_substr($fax, 0, $start);

          $end = mb_strpos($fax, ")");
          $codeOperator = mb_substr($fax, $start + 1, $end - $start - 1);

          $number = mb_substr($fax, $end + 1);
          $number = str_replace(["-", " "], "", $number);

          // if number contains ',' -> 2 numbers
          if (mb_strpos($number, ","))
          {
            $numbers = explode(",", $number);

            foreach ($numbers as $key => $num)
            {
              $db[$i]["faxes"][] = [
                "type" => "fax",
                "code_country" => trim($codeCountry),
                "code_operator" => trim($codeOperator),
                "number" => trim($num)
              ];
            }
          }
          else
          {
            $db[$i]["faxes"][] = [
              "type" => "fax",
              "code_country" => trim($codeCountry),
              "code_operator" => trim($codeOperator),
              "number" => trim($number)
            ];
          }
        }
      }

      $i += 1;
    }

    return $db;
  }

  private function parseShymkent()
  {
    $files = File::allFiles(public_path() . '/data/shymkent/');
    $bar = $this->output->createProgressBar(count($files));

    foreach ($files as $file)
    {
          $bar->advance();
          $data = json_decode(File::get($file));

          $orgCategoryId = 0;

          if (isset($this->categoriesMap[$data->category])) 
          {
                $orgCategoryId = $this->categoriesMap[$data->category];
          }
          // $this->line($orgCategoryId); dd("");

          if ($orgCategoryId == 0) continue;

          $orgExists = DB::table('organizations')->where('name', $data->name)->first();
          if ($orgExists)
          {       
                $branch = DB::table('branches')->where('organization_id', $orgExists->id)->first();
                
                if ($branch)
                {
                      $pivot = DB::table('branch_category')->where('branch_id', $branch->id)->where('category_id', $orgCategoryId)->first();

                      if (!$pivot) DB::table('branch_category')->insert(['branch_id' => $branch->id, 'category_id' => $orgCategoryId]);
                }                              
          }
    }
    
    $bar->finish();
    return;



    $urls = [
          // 'http://www.e-shymkent.kz/catalog/automobile/autoservice/1/0/',
          // 'http://www.e-shymkent.kz/catalog/automobile/autoservice/1/1/',
          // 'http://www.e-shymkent.kz/catalog/automobile/autoservice/1/2/',

          // 'http://www.e-shymkent.kz/catalog/automobile/refuelling/1/0/',
          // 'http://www.e-shymkent.kz/catalog/automobile/refuelling/1/1/',
          // 'http://www.e-shymkent.kz/catalog/automobile/refuelling/1/2/',

          // 'http://www.e-shymkent.kz/catalog/automobile/accessory/1/0/',
          // 'http://www.e-shymkent.kz/catalog/automobile/accessory/1/1/',
          // 'http://www.e-shymkent.kz/catalog/automobile/accessory/1/2/',

          // 'http://www.e-shymkent.kz/catalog/automobile/traffic/1/0/',
          // 'http://www.e-shymkent.kz/catalog/automobile/traffic/1/1/',
          // 'http://www.e-shymkent.kz/catalog/automobile/traffic/1/2/',
          // 'http://www.e-shymkent.kz/catalog/automobile/traffic/1/3/',

          // 'http://www.e-shymkent.kz/catalog/automobile/autosales/1/0/',
          // 'http://www.e-shymkent.kz/catalog/automobile/autosales/1/1/',

          // 'http://www.e-shymkent.kz/catalog/security/oborudovanie',
          // 'http://www.e-shymkent.kz/catalog/security/organizacii',

          // 'http://www.e-shymkent.kz/catalog/business/consulting',

          // 'http://www.e-shymkent.kz/catalog/business/pension/1/0/',
          // 'http://www.e-shymkent.kz/catalog/business/pension/1/1/',

          // 'http://www.e-shymkent.kz/catalog/business/banks',

          // 'http://www.e-shymkent.kz/catalog/business/exchange',

          // 'http://www.e-shymkent.kz/catalog/business/microcredit/1/0/',
          // 'http://www.e-shymkent.kz/catalog/business/microcredit/1/1/',
          // 'http://www.e-shymkent.kz/catalog/business/microcredit/1/2/',

          // 'http://www.e-shymkent.kz/catalog/state/apparat',
          // 'http://www.e-shymkent.kz/catalog/state/akim-goroda',
          // 'http://www.e-shymkent.kz/catalog/state/ngo',
          // 'http://www.e-shymkent.kz/catalog/state/bodies/1/0/',
          // 'http://www.e-shymkent.kz/catalog/state/bodies/1/1/',
          // 'http://www.e-shymkent.kz/catalog/state/bodies/1/2/',
          // 'http://www.e-shymkent.kz/catalog/state/police',

          // 'http://www.e-shymkent.kz/catalog/computers/technics/1/0/',
          // 'http://www.e-shymkent.kz/catalog/computers/technics/1/1/',

          // 'http://www.e-shymkent.kz/catalog/computers/software',

          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/0/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/1/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/2/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/3/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/4/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/5/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/6/',
          // 'http://www.e-shymkent.kz/catalog/health/drugs/1/7/',

          // 'http://www.e-shymkent.kz/catalog/health/medoborud',
          // 'http://www.e-shymkent.kz/catalog/health/veterinary',
          // 'http://www.e-shymkent.kz/catalog/health/optics',
          // 'http://www.e-shymkent.kz/catalog/health/medical/1/0/',
          // 'http://www.e-shymkent.kz/catalog/health/medical/1/1/',
          // 'http://www.e-shymkent.kz/catalog/health/medical/1/2/',

          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/0/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/1/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/2/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/3/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/4/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/5/',
          // 'http://www.e-shymkent.kz/catalog/health/beauty/1/6/',

          // 'http://www.e-shymkent.kz/catalog/health/medcenters/1/0/',
          // 'http://www.e-shymkent.kz/catalog/health/medcenters/1/1/',
          // 'http://www.e-shymkent.kz/catalog/health/medcenters/1/2/',

          // 'http://www.e-shymkent.kz/catalog/health/stomatology/1/0/',
          // 'http://www.e-shymkent.kz/catalog/health/stomatology/1/1/',
          // 'http://www.e-shymkent.kz/catalog/health/stomatology/1/2/',
           
          // 'http://www.e-shymkent.kz/catalog/arts/library',
          // 'http://www.e-shymkent.kz/catalog/arts/cinema',

          // 'http://www.e-shymkent.kz/catalog/goods/audiovideo',
          // 'http://www.e-shymkent.kz/catalog/goods/condition',
          // 'http://www.e-shymkent.kz/catalog/goods/stationery',

          // 'http://www.e-shymkent.kz/catalog/goods/furniture/1/0/',
          // 'http://www.e-shymkent.kz/catalog/goods/furniture/1/1/',
          // 'http://www.e-shymkent.kz/catalog/goods/furniture/1/2/',

          // 'http://www.e-shymkent.kz/catalog/science/outdoors/1/0/',
          // 'http://www.e-shymkent.kz/catalog/science/outdoors/1/1/',

          // 'http://www.e-shymkent.kz/catalog/science/college/1/0/',
          // 'http://www.e-shymkent.kz/catalog/science/college/1/1/',

          // 'http://www.e-shymkent.kz/catalog/science/university',

          // 'http://www.e-shymkent.kz/catalog/science/courses/1/0/',
          // 'http://www.e-shymkent.kz/catalog/science/courses/1/1/',

          // 'http://www.e-shymkent.kz/catalog/science/kindergarden',

          // 'http://www.e-shymkent.kz/catalog/science/schools/1/0/',
          // 'http://www.e-shymkent.kz/catalog/science/schools/1/1/',
          // 'http://www.e-shymkent.kz/catalog/science/schools/1/2/',
          // 'http://www.e-shymkent.kz/catalog/science/schools/1/3/',
          // 'http://www.e-shymkent.kz/catalog/science/schools/1/4/',

          // 'http://www.e-shymkent.kz/catalog/equipment/eqother/1/0/',
          // 'http://www.e-shymkent.kz/catalog/equipment/eqother/1/1/',

          // 'http://www.e-shymkent.kz/catalog/equipment/commercial',
          // 'http://www.e-shymkent.kz/catalog/equipment/packaging',
          // 'http://www.e-shymkent.kz/catalog/equipment/electrotechnology/1/0/',
          // 'http://www.e-shymkent.kz/catalog/equipment/electrotechnology/1/1/',

          // 'http://www.e-shymkent.kz/catalog/industry/metal/1/0/',
          // 'http://www.e-shymkent.kz/catalog/industry/metal/1/1/',
          // 'http://www.e-shymkent.kz/catalog/industry/metal/1/2/',
          // 'http://www.e-shymkent.kz/catalog/industry/metal/1/3/',

          // 'http://www.e-shymkent.kz/catalog/industry/industry',
          // 'http://www.e-shymkent.kz/catalog/industry/foodprom/1/0/',
          // 'http://www.e-shymkent.kz/catalog/industry/foodprom/1/1/',

          // 'http://www.e-shymkent.kz/catalog/industry/agriculture',
          // 'http://www.e-shymkent.kz/catalog/industry/production/1/0/',
          // 'http://www.e-shymkent.kz/catalog/industry/production/1/1/',

          // 'http://www.e-shymkent.kz/catalog/massmedia/printing',
          // 'http://www.e-shymkent.kz/catalog/massmedia/press',
          // 'http://www.e-shymkent.kz/catalog/massmedia/advertising/1/0/',
          // 'http://www.e-shymkent.kz/catalog/massmedia/advertising/1/1/',
          // 'http://www.e-shymkent.kz/catalog/massmedia/advertising/1/2/',

          // 'http://www.e-shymkent.kz/catalog/building/materials/1/0/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/1/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/2/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/3/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/4/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/5/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/6/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/7/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/8/',
          // 'http://www.e-shymkent.kz/catalog/building/materials/1/9/',

          // 'http://www.e-shymkent.kz/catalog/building/projecting/1/0/',
          // 'http://www.e-shymkent.kz/catalog/building/projecting/1/1/',
          // 'http://www.e-shymkent.kz/catalog/building/projecting/1/2/',

          // 'http://www.e-shymkent.kz/catalog/building/realestate/1/0/',
          // 'http://www.e-shymkent.kz/catalog/building/realestate/1/1/',

          // 'http://www.e-shymkent.kz/catalog/building/construction/1/0/',
          // 'http://www.e-shymkent.kz/catalog/building/construction/1/1/',
          // 'http://www.e-shymkent.kz/catalog/building/construction/1/2/',
          // 'http://www.e-shymkent.kz/catalog/building/construction/1/3/',

          // 'http://www.e-shymkent.kz/catalog/communication/internet',
          // 'http://www.e-shymkent.kz/catalog/communication/mobile/1/0/',
          // 'http://www.e-shymkent.kz/catalog/communication/mobile/1/1/',
          // 'http://www.e-shymkent.kz/catalog/communication/mobile/1/2/',

          // 'http://www.e-shymkent.kz/catalog/trade/books',

          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/0/',
          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/1/',
          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/2/',
          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/3/',
          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/4/',
          // 'http://www.e-shymkent.kz/catalog/trade/clothes/1/5/',

          // 'http://www.e-shymkent.kz/catalog/trade/discs',
          // 'http://www.e-shymkent.kz/catalog/trade/children',
          // 'http://www.e-shymkent.kz/catalog/trade/confectionery',
          // 'http://www.e-shymkent.kz/catalog/trade/curtains/1/0/',
          // 'http://www.e-shymkent.kz/catalog/trade/curtains/1/1/',

          // 'http://www.e-shymkent.kz/catalog/trade/cosmetics',
          // 'http://www.e-shymkent.kz/catalog/trade/jewelry',
          
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/0/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/1/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/2/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/3/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/4/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/5/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/6/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/7/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/8/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/9/',
          // 'http://www.e-shymkent.kz/catalog/trade/stores/1/10/',

          // 'http://www.e-shymkent.kz/catalog/rest/airlines/1/2/',
          // 'http://www.e-shymkent.kz/catalog/rest/airlines/1/1/',
          // 'http://www.e-shymkent.kz/catalog/rest/airlines/1/2/',

          // 'http://www.e-shymkent.kz/catalog/rest/parks',

          // 'http://www.e-shymkent.kz/catalog/rest/baths/1/1/',
          // 'http://www.e-shymkent.kz/catalog/rest/baths/1/1/',

          // 'http://www.e-shymkent.kz/catalog/rest/sportsclub',
          // 'http://www.e-shymkent.kz/catalog/rest/hotels/1/0/',
          // 'http://www.e-shymkent.kz/catalog/rest/hotels/1/1/',

          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/0/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/1/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/2/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/3/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/4/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/5/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/6/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/7/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/8/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/9/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/10/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/11/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/cafe/1/12/',

          // 'http://www.e-shymkent.kz/catalog/entertainment/restaurant/1/1/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/restaurant/1/2/',
          // 'http://www.e-shymkent.kz/catalog/entertainment/restaurant/1/3/',

          // 'http://www.e-shymkent.kz/catalog/entertainment/discos',

          // 'http://www.e-shymkent.kz/catalog/services/consultations',
          // 'http://www.e-shymkent.kz/catalog/services/waterpipe',
          
          // 'http://www.e-shymkent.kz/catalog/services/post/1/0/',
          // 'http://www.e-shymkent.kz/catalog/services/post/1/1/',

          // 'http://www.e-shymkent.kz/catalog/services/design',
          // 'http://www.e-shymkent.kz/catalog/services/tailoring/1/0/',
          // 'http://www.e-shymkent.kz/catalog/services/tailoring/1/1/',
          // 'http://www.e-shymkent.kz/catalog/services/tailoring/1/2/',

          // 'http://www.e-shymkent.kz/catalog/services/stamps',
          // 'http://www.e-shymkent.kz/catalog/services/repairs',
          // 'http://www.e-shymkent.kz/catalog/services/compservices',
          // 'http://www.e-shymkent.kz/catalog/services/studios',
          // 'http://www.e-shymkent.kz/catalog/services/catering',
          // 'http://www.e-shymkent.kz/catalog/services/drycleaners',

          // 'http://www.e-shymkent.kz/catalog/services/wedding/1/0/',
          // 'http://www.e-shymkent.kz/catalog/services/wedding/1/1/',

          // 'http://www.e-shymkent.kz/catalog/services/legal/1/0/',
          // 'http://www.e-shymkent.kz/catalog/services/legal/1/1/',
          // 'http://www.e-shymkent.kz/catalog/services/legal/1/2/',
          // 'http://www.e-shymkent.kz/catalog/services/legal/1/3/',
          // 'http://www.e-shymkent.kz/catalog/services/legal/1/4/',
    ];
  
    $result = [];
  
    foreach ($urls as $url) 
    {
          $data = $this->crawl($url);
          $result[] = $data;
    }
  
    $bar = $this->output->createProgressBar(count($result));
    foreach ($result as $items)
    {
          foreach ($items as $item)
          {
                // append to a file
                File::append(public_path() . '/data/shymkent/' . uniqid() . ".json", json_encode($item));
          }

          $bar->advance();
    }

    $bar->finish();
  }

  private function orgPageCategories()
  {
    $count = DB::table('parsedobjects')->count();
    $bar = $this->output->createProgressBar($count);

    DB::table('parsedobjects')->chunk(1000, function($objs) use (&$bar)
    {
      foreach ($objs as $obj)
      {
        $orgCategory = "";
        $categories = json_decode($obj->categories);
        // dd($this->decodeUnicode($categories[0]));
      
        if (!empty($categories))
        {
          $orgCategory = $this->decodeUnicode($categories[0]);
        }

        File::append(public_path() . '/data/orgpage_cats.txt', $orgCategory . "\n");
        $bar->advance();
      }
    });

    $bar->finish();
  }

  private function getFromGis($rubricsFile)
  {
    // $src = File::get(public_path() . "/data/rubrics-astana.js");
    $src = File::get(public_path() . "/data/" . $rubricsFile);
    $data = json_decode($src);
    // dd($data);

    $rubrics = [];
    $orgCount = 0;
    $branchCount = 0;

    foreach ($data->result->items as $item)
    {
      foreach ($item->rubrics as $rubric)
      {
        $rubrics[] = [
            'id' => $rubric->id,
            'name' => $rubric->name,
            'org_count' => $rubric->org_count,
            'branch_count' => $rubric->branch_count,
            'parent' => $item->name
        ];

        $branchCount += $item->branch_count;
      }

      $orgCount += $item->org_count;
    }

    // dd([$orgCount, $branchCount, $rubrics]);
    $loadedRubrics = [];
    
    $length = count($rubrics);
    foreach ($rubrics as $key => $rubric)
    {
      // if (in_array($rubric['id'], $loadedRubrics)) continue;
      // if ($key < 190) continue;
      // if (in_array($rubric['name'], $loadedRubrics)) continue;
      // $this->info($rubric['id']);dd('...');

      $proxy = $this->getRandomProxy();
      // dd($proxy);

      $opts = array(
        'https'=>array(
            'method'=>"GET",
            'proxy' => $proxy,
        ),
        "ssl" => array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        )
      );
      $context = stream_context_create($opts);

      $data = file_get_contents("https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=1&page_size=12&rubric_id=" . $rubric['id'] . "&hash=63fbd10ac274911c&stat%5Bpr%5D=3&region_id=84&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Ccontext_rubrics%2Chash%2Csearch_attributes&key=ruczoy1743", false, $context);

      $data = json_decode($data);

      if ($data->meta->code == 404) 
      {
        $this->info("no results for " . $rubric['id']);
        continue;
      }

      $total = $data->result->total;
      $numOfPages = intval(ceil($total / 50.0));

      if ($numOfPages == 1)
      {
        $this->downloadItems($rubric['id'], 1);
      }
      else
      {
        for ($i = 1; $i <= $numOfPages; $i++) 
        {
          $this->downloadItems($rubric['id'], $i);
        }
      }

      $this->info("DONE rubric. Left: " . ($key + 1) . "/" . $length);
    }
  }

  private function downloadItems($rubricId, $pageNum)
  {
    $proxy = $this->getRandomProxy();

    $path = public_path() . "/data/gis/";
    $url = "https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=" . $pageNum . "&page_size=12&rubric_id=" . $rubricId . "&hash=63fbd10ac274911c&stat%5Bpr%5D=3&region_id=84&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Ccontext_rubrics%2Chash%2Csearch_attributes&key=ruczoy1743";

    $opts = array(
      'https' => array(
        'method'=>"GET",
        'proxy' => $proxy,
      ),
      "ssl" => array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      )
    );
    $context = stream_context_create($opts);

    $data = file_get_contents($url, false, $context);
    file_put_contents($path . $rubricId . "-" . $pageNum . ".json", $data);
  }

  private function getRandomProxy()
  {
    $proxies = [];

    $handle = fopen(public_path() . "/data/proxies.txt", "r");
    if ($handle) 
    {
      while (($line = fgets($handle)) !== false) 
      {
          $proxies[] = trim($line);
      }

      fclose($handle);
    }

    return $proxies[array_rand($proxies)];
  }


    private function seedGis()
    {
        // $this->removeOrganizations();

        // $limit = $this->argument('limit');

        $cities = [
            '67' => 3,
            '68' => 2,
        ];

        $count = 0;

        $pathes = [
            public_path() . "/data/gis-astana/part-1/",
            public_path() . "/data/gis-astana/part-2/",
            public_path() . "/data/gis-astana/part-3/",
        ];

        $allCategories = [];
        $total = 42293;
        $bar = $this->output->createProgressBar($total);
        $inc = 0;

        foreach ($pathes as $path)
        {
            $files = File::allFiles($path);

            foreach ($files as $file)
            {
                $data = json_decode(File::get($file));

                foreach ($data->result->items as $key => $object)
                {
                    // dd($object);
                    // if ($key < 4) continue;
                    // $inc += 1;

                    // if ($inc <= 21194) continue;

                    try
                    {
                        $orgCityId = "";
                        $orgUrl = "";
                        $orgCategory = "";
                        $orgAddress = "";
                        $orgWorkingHours = "";
                        $orgContacts = [];
                        $orgSite = "";
                        $orgEmail = "";
                        $orgDescription = "";
                        $orgName = "";
                        $orgLat = "";
                        $orgLng = "";

                        // dd($object);

                        if ($object->name == "" || $object->name == "БЕЗ НАЗВАНИЯ") continue;

                        $city = $object->region_id;
                        $orgId = $object->org->id;
                        $orgCityId = $cities[$city];
                        // dd([$orgId, $orgCityId]);
                        
                        // categories
                        $categories = [];
                        foreach ($object->rubrics as $rubric)
                        {
                            // dd($rubric);
                            if (isset($this->categoriesMap[$rubric->name]))
                            {
                                // dd($this->categoriesMap[$rubric->name]);
                                $categories[] = $this->categoriesMap[$rubric->name];
                            }
                        }
                        // dd($categories);

                        // address
                        $orgAddress = isset($object->address_name) ? $object->address_name : "";
                        // dd($orgAddress);
                        
                        $weekDays = [
                            "Mon" => "Пн", "Tue" => "Вт", "Wed" => "Ср", "Thu" => "Чт", 
                            "Fri" => "Пт", "Sat" => "Сб", "Sun" => "Вс"
                        ];

                        // working hours
                        $workingHours = [];
                        if (isset($object->schedule))
                        {
                            foreach ($object->schedule as $day => $whs)
                            {
                                if ($day == "comment") continue;

                                foreach ($whs as $wh)
                                {
                                    $from = $to = "";

                                    if (isset($wh[0])) $from = $weekDays[$day] . ": " . $wh[0]->from . "-" . $wh[0]->to;
                                    if (isset($wh[1])) $to = $wh[1]->from . "-" . $wh[1]->to;

                                    $workingHours[] = $from . " " . $to;
                                }
                            }
                        }
                        
                        // dd($workingHours);
                        $orgWorkingHours = isset($object->schedule) ? implode("\n", $workingHours) : "";
                        
                        // contacts
                        $orgContacts = [];
                        $orgSites = [];

                        foreach ($object->contact_groups as $contactGroup)
                        {
                            foreach ($contactGroup->contacts as $contact)
                            {
                                if ($contact->type == "phone" || $contact->type == "fax") 
                                    $orgContacts[] = $contact;

                                if ($contact->type == "website" || $contact->type == "instagram" ||
                                    $contact->type == "vkontakte" || $contact->type == "twitter" ||
                                    $contact->type == "facebook") 
                                    $orgSites[] = $contact;
                            }                   
                        }
                        // dd($orgContacts);

                        // email
                        $orgEmail = "";

                        // description & production
                        $orgDescription = "";
                        // dd($orgDescription);

                        // lat
                        $orgLat = isset($object->point->lat) ? $object->point->lat : "0.00";
                        // dd($orgLat);

                        // lng
                        $orgLng = isset($object->point->lon) ? $object->point->lon : "0.00";
                        // dd($orgLng);

                        // name
                        $orgName = $object->name;
                        // dd($orgName);
                        
                        // insert to organizations, branches, phones, socials
                        DB::beginTransaction();

                        // $orgExists = Organization::where('notes', $orgId)->first();
                        // if ($orgExists) continue;

                        $organization = Organization::create([
                            "name"          => $orgName,
                            "type"          => "custom",
                            "description"   => $orgDescription,
                            "status"        => 'published',
                            "notes"         => $orgId
                        ]);
                        // dd($organization->toArray());
                        
                        $postcode = isset($object->address->postcode) ? $object->address->postcode : "";

                        $branch = Branch::create([
                            "organization_id"   => $organization->id,
                            "type"              => "main",
                            "name"              => $organization->name,
                            "description"       => $object->id,
                            "city_id"           => $orgCityId,
                            "address"           => $orgAddress,
                            "post_index"        => $postcode,
                            "email"             => $orgEmail,
                            "hits"              => 0,
                            "lat"               => $orgLat,
                            "lng"               => $orgLng,
                            "working_hours"     => $orgWorkingHours,
                            "status"            => "published"
                        ]);
                        // dd($branch->toArray());

                        // map branch to category!
                        foreach ($categories as $categoryId)
                        {
                            $exists = DB::table('branch_category')
                                ->where('branch_id', $branch->id)
                                ->where('category_id', $categoryId)
                                ->first();

                            if (!$exists)
                            {
                                $pivotRecord = DB::table("branch_category")->insert([
                                    "branch_id"   => $branch->id,
                                    "category_id" => $categoryId
                                ]);
                                // dd($pivotRecord);
                            }
                        }                        

                        foreach ($orgContacts as $phone)
                        {
                            $type = $codeCountry = $codeOperator = $number = "";

                            // type
                            // $fb = strpos($phone, "(");
                            // $lb = strpos($phone, ")");
                            // $codeOperator = substr($phone, $fb + 1, $lb - $fb - 1);
                            
                            // number
                            // $number = substr($phone, $lb + 1);
                            // $number = str_replace("-", "", trim($number));
                            $number = $phone->value;
                            // dd($number);

                            // if (!empty($codeOperator) && strlen($codeOperator) == 3 && $codeOperator[1] != "1" && $codeOperator[1] != "2")
                            // {
                            //     $type = "mobile";
                            // }
                            // else
                            // {
                            //     $type = "work";
                            // }
                            // dd($type);
                            $type = ($phone->type == "fax") ? "fax" : "work";
                            // dd($type);

                            $phoneRecord = Phone::create([
                                "branch_id"      => $branch->id,
                                "type"           => $type,
                                "code_country"   => "+7",
                                "code_operator"  => "fix",
                                "number"         => $number,
                                "contact_person" => isset($phone->comment) ? $phone->comment : ""
                            ]);
                            // dd($phoneRecord->toArray());
                        }

                        // dd($orgSites);
                        foreach ($orgSites as $site)
                        {
                            $socialRecord = Social::create([
                                "branch_id"       => $branch->id,
                                "type"            => $site->type,
                                "name"            => $site->text,
                                "contact_person"  => ""
                            ]);
                            // dd($socialRecord->toArray());
                        }

                        DB::commit();
                        $this->info("Inserted " . $inc);
                    }
                    catch (Exception $e)
                    {
                        $this->error("Error: " . $e->getMessage());
                    }
                    
                    $bar->advance();
                }
            }

            // dd("DONE PART 1");
        }

        $bar->finish();
    }


      private function parseOrgPages()
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
            
            $count = DB::table('parsedobjects')->count();
            $bar = $this->output->createProgressBar($count);

            // $orgs = [];
            // $orgsDB = DB::table('organizations')->where('notes', null)->get();
            // foreach ($orgsDB as $org)
            // {
            //       $orgs[$org->name] = $org;
            // }
            // foreach ($orgs as $key => $org) { dd($key); }

            DB::table('parsedobjects')->chunk(1000, function($objs) use (&$bar, &$cities, &$orgrs)
            {
                  foreach ($objs as $object)
                  {
                        $bar->advance();

                        if ($object->name == "" || $object->name == "БЕЗ НАЗВАНИЯ") continue;

                        $field = json_decode($object->url);
                        $city = $field->city;

                        // city & url (notes)
                        $orgUrl = $field->url;
                        $orgCityId = $cities[$city];

                        // skip Shymkent, Astana & Almaty objects
                        if (in_array($orgCityId, [1, 2, 3])) continue;

                        // categories
                        $categories = json_decode($object->categories);
                        // dd($this->decodeUnicode($categories[0]));
                
                        if (!empty($categories))
                        {
                              $orgCategory = $this->decodeUnicode($categories[0]);
                        }

                        $orgCategoryId = 0;
                        if (isset($this->categoriesMap[$orgCategory])) $orgCategoryId = $this->categoriesMap[$orgCategory];
                        if ($orgCategoryId == 0) continue;

                        // name
                        $orgName = $object->name;

                        // orgs exists
                        $orgExists = DB::table('organizations')->where('name', $orgName)->first();
                        if ($orgExists)
                        {       
                              $branch = DB::table('branches')->where('organization_id', $orgExists->id)->first();
                              
                              if ($branch)
                              {
                                    $pivot = DB::table('branch_category')->where('branch_id', $branch->id)->where('category_id', $orgCategoryId)->first();

                                    if (!$pivot) DB::table('branch_category')->insert(['branch_id' => $branch->id, 'category_id' => $orgCategoryId]);
                              }                              
                        }
                  }
            });

            $bar->finish();
      }


    private function seed()
    {
        // $this->removeOrganizations();

        $limit = $this->argument('limit');

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

        $allCategories = [];
        $total = 15829;

        DB::table('parsedobjects')->where('id', '>', 6765)->chunk($limit, function($objects) use ($limit, $cities, &$allCategories, &$total)
        {
            $allContacts = [];

            foreach ($objects as $key => $object) 
            {
                try
                {
                    $orgCityId = "";
                    $orgUrl = "";
                    $orgCategory = "";
                    $orgAddress = "";
                    $orgWorkingHours = "";
                    $orgContacts = [];
                    $orgSite = "";
                    $orgEmail = "";
                    $orgDescription = "";
                    $orgName = "";
                    $orgLat = "";
                    $orgLng = "";

                    // dd($object);

                    if ($object->name == "" || $object->name == "БЕЗ НАЗВАНИЯ") continue;

                    $field = json_decode($object->url);
                    $city = $field->city;

                    // city & url (notes)
                    $orgUrl = $field->url;
                    $orgCityId = $cities[$city];
                    // dd([$orgUrl, $orgCityId]);
                    
                    // categories
                    $categories = json_decode($object->categories);
                    // dd($this->decodeUnicode($categories[0]));
                
                    if (!empty($categories))
                    {
                        $orgCategory = $this->decodeUnicode($categories[0]);
                    }

                    // address
                    $address = json_decode($object->address);
                    // if (count($address) > 0) dd($this->decodeUnicode($address[count($address) - 1]));

                    if (!empty($address))
                    {
                        $orgAddress = $this->decodeUnicode($address[count($address) - 1]);
                    }

                    // working hours
                    $orgWorkingHours = $object->workinghours;
                    // dd($orgWorkingHours);
                    
                    // contacts
                    $contacts = json_decode($object->contacts);
                    // if (count($contacts) > 0) dd($contacts);

                    if (!empty($contacts)) 
                    {
                        foreach ($contacts as $contact)
                        {
                            if (strlen($contact) > 3 && strpos($contact, "u") === false)
                            {
                              $orgContacts[] = $contact;
                            }
                        }
                    }

                    // sites
                    $sites = json_decode($object->sites);
                    // if (count($sites) > 0) dd($sites);

                    if (!empty($sites)) $orgSite = $sites[0];

                    // emails
                    $emails = json_decode($object->emails);
                    // if (count($emails) > 0) dd($emails);

                    if (!empty($emails)) $orgEmail = $emails[0];

                    // description & production
                    $orgDescription = $object->description . "\n" . $object->production;
                    // dd($orgDescription);

                    // lat
                    $lat = json_decode($object->lat);
                    // if (count($lat) > 0) dd($lat[0]);

                    if (!empty($lat)) $orgLat = $lat[0];

                    // lng
                    $lng = json_decode($object->lng);
                    // if (count($lng) > 0) dd($lng[0]);

                    if (!empty($lng)) $orgLng = $lng[0];

                    // name
                    $orgName = $object->name;
                    // dd($orgName);

                
                    // dd([
                    //   'cityId' => $orgCityId,
                    //   'category' => $orgCategory,
                    //   'name' => $orgName,
                    //   'description' => $orgDescription,
                    //   'address' => $orgAddress,
                    //   'workinghours' => $orgWorkingHours,
                    //   'contacts' => $orgContacts,
                    //   'site' => $orgSite,
                    //   'email' => $orgEmail,
                    //   'lat' => $orgLat,
                    //   'lng' => $orgLng,
                    //   'url' => $orgUrl
                    // ]);

                    // insert to organizations, branches, phones, socials
                    DB::beginTransaction();

                    $dbCategory = Category::whereName($orgCategory)->first();
                    if (!$dbCategory) 
                    {
                        $rootCategory = Category::findOrFail(1);

                        $dbCategory = Category::create([
                            'name' => $orgCategory, 
                            'icon' => 'noicon.png', 
                            'slug' => $this->sluggify($orgCategory)
                        ]);

                        $dbCategory->makeChildOf($rootCategory);
                        $dbCategory->save();
                    }
                    // dd($dbCategory->toArray());

                    $organization = Organization::create([
                        "name"          => $orgName,
                        "type"          => "custom",
                        "description"   => $orgDescription,
                        "status"        => 'published',
                        "notes"         => $orgUrl
                    ]);
                    // dd($organization->toArray());
                 
                    $branch = Branch::create([
                        "organization_id"   => $organization->id,
                        "type"              => "main",
                        "name"              => $organization->name,
                        "description"       => $organization->description,
                        "city_id"           => $orgCityId,
                        "address"           => $orgAddress,
                        "post_index"        => "",
                        "email"             => $orgEmail,
                        "hits"              => 0,
                        "lat"               => $orgLat,
                        "lng"               => $orgLng,
                        "working_hours"     => $orgWorkingHours,
                        "status"            => "published"
                    ]);
                    // dd($branch->toArray());

                    // map branch to category!
                    $pivotRecord = DB::table("branch_category")->insert([
                        "branch_id"   => $branch->id,
                        "category_id" => $dbCategory->id
                    ]);
                    // dd($pivotRecord);

                    if (!empty($orgContacts))
                    {
                        foreach ($orgContacts as $phone)
                        {
                            $type = $codeCountry = $codeOperator = $number = "";

                            // type
                            $fb = strpos($phone, "(");
                            $lb = strpos($phone, ")");
                            $codeOperator = substr($phone, $fb + 1, $lb - $fb - 1);
                            
                            // number
                            $number = substr($phone, $lb + 1);
                            $number = str_replace("-", "", trim($number));
                            // dd($number);

                            if (!empty($codeOperator) && strlen($codeOperator) == 3 && $codeOperator[1] != "1" && $codeOperator[1] != "2")
                            {
                                $type = "mobile";
                            }
                            else
                            {
                                $type = "work";
                            }
                            // dd($type);

                            $phoneRecord = Phone::create([
                                "branch_id"      => $branch->id,
                                "type"           => $type,
                                "code_country"   => "+7",
                                "code_operator"  => $codeOperator,
                                "number"         => $number,
                                "contact_person" => ""
                            ]);
                            // dd($phoneRecord->toArray());
                        }
                    }

                    if (!empty($orgSite))
                    {
                        $socialRecord = Social::create([
                            "branch_id"       => $branch->id,
                            "type"            => "website",
                            "name"            => $orgSite,
                            "contact_person"  => ""
                        ]);
                    }
                    
                    // dd($socialRecord->toArray());

                    DB::commit();
                    $total -= 1;
                }
                catch (Exception $e)
                {
                    $this->error("Error: " . $e->getMessage());
                }
            }

            $this->info("LEFT: " . $total);
        });

        $this->info("DONE");
    }

    private function removeOrganizations()
    {
        try 
        {
            $lastId = Organization::orderBy('id', 'DESC')->first()->id;

            $ids = [];
            for ($i = 2810; $i <= $lastId; $i++)
            {
                $ids[] = $i;
            }

            $organizations = Organization::whereIn('id', $ids)->get();
            // dd(count($organizations));

            foreach ($organizations as $organization)
            {
                foreach ($organization->branches as $branch)
                {
                    foreach ($branch->phones as $key => $phone) 
                    {
                        $phone->delete();
                    }

                    foreach ($branch->socials as $key => $social) 
                    {
                        $social->delete();
                    }

                    foreach ($branch->photos as $key => $photo) 
                    {
                        File::delete(public_path() . "/images/photos/" . $photo->path);
                        $photo->delete();
                    }

                    $branch->delete();
                }

                $organization->delete();
            }

            $this->info("DONE");
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    private function parse()
    {
        $limit = $this->argument('limit');

        DB::table('urls')->where("parsed", 0)->chunk($limit, function($urls) use ($limit)
        {
            $needCity = false;
            $cities = [
                "kyizyilorda",
                "taraz",
                "almatyi",
                "karaganda", 
                "astana",
                "ust-kamenogorsk", 
                "semey",
                "kokshetau",
                "kostanay",
                "aktyubinsk",
                "uralsk",
                "atyirau",
                "aktau",
                "shyimkent",
            ];

            // $proxies = [
            //     '5.196.94.27:3128',
            //     "200.240.248.234:3128",
            //     "5.196.88.157:3128",
            //     "191.102.110.22:3128",
            //     "75.76.162.43:3128",
            //     "188.166.188.166:80",
            //     "103.250.58.52:3128",
            //     "161.139.251.106:9000",
            //     "179.159.63.237:3128",
            //     "37.61.251.177:8080",
            //     "5.39.72.58:3128",
            //     "193.200.83.243:8080",
            //     "52.87.240.241:3128",
            //     "5.39.74.118:3128",
            //     "117.16.46.63:3128",
            //     "200.45.54.139:3128",
            // ];
            $proxies = [];

            $handle = fopen(public_path() . "/data/proxies.txt", "r");
            if ($handle) 
            {
                while (($line = fgets($handle)) !== false) 
                {
                    $proxies[] = trim($line);
                }

                fclose($handle);
            }

            $proxy = $proxies[array_rand($proxies)];
            // dd($proxy);

            $orgs = [];

            $updateUrlIds = [];
            foreach ($urls as $url) 
            {
                $updateUrlIds[] = $url->id;
            }
            DB::table('urls')->whereIn('id', $updateUrlIds)->update(['parsed' => 1]);

            foreach ($urls as $key => $url) 
            {
                // DB::table('urls')->where("id", $url->id)->update(['parsed' => 1]);

                // check for city
                $whichCity = "";
                $needCity = false;

                foreach ($cities as $city)
                {
                    if (strpos($url->url, $city) !== false) 
                    {
                        $whichCity = $city;
                        $needCity = true;
                        break;
                    }
                }

                if ($needCity == false) continue;

                $client = new Client();
                $guzzle = $client->getClient(["proxy" => $proxy]);
                $client->setClient($guzzle);

                // $url = new StdClass;
                // $url->id = 1;
                // $url->url = "http://orgpage.kz/otegen-batyir/almatysystem-2653039.html";
                // $url->url = "http://orgpage.kz/astana/uk-shanyrak-2564149.html";
                
                $crawler = $client->request('GET', $url->url);       
                $status_code = $client->getResponse()->getStatus();
                // dd($status_code);

                $name = "";
                $categories = [];
                $address = [];
                $workingHours = "";
                $contacts = [];
                $sites = [];
                $emails = [];
                $description = "";
                $production = "";
                $lat = "";
                $lng = "";

                if ($status_code == 200)
                {
                    // name
                    $nameNode = $crawler->filter('h1.profile[itemprop=name]');
                    $name = ($nameNode->count() > 0) ? $nameNode->text() : "БЕЗ НАЗВАНИЯ";

                    // categories
                    $categoriesNode = $crawler->filter('#list_rubrics a');
                    if ($categoriesNode->count() > 0)
                    {
                        $categories = $categoriesNode->each(function($node, $i)
                        {
                            return $node->text();
                        });
                    }       
                    // dd($categories);

                    // address
                    $addressNode = $crawler->filter('#list_address span');
                    if ($addressNode->count() > 0)
                    {
                        $address = $addressNode->each(function($node, $i)
                        {
                            return $node->text();
                        });
                    }
                    // dd($address);
                    
                    // working hours
                    $workingHoursNode = $crawler->filter('#workinghours');
                    if ($workingHoursNode->count() > 0) $workingHours = $workingHoursNode->text();
                    // dd($workingHours);

                    // list_contact_phone
                    $contactsNode = $crawler->filter('#list_contact_phone #phones td');
                    if ($contactsNode->count() > 0)
                    {
                        $contacts = $contactsNode->each(function($node, $i)
                        {
                            return trim($node->text());
                        });
                    }
                    // dd($contacts);

                    // list_sites
                    $sitesNode = $crawler->filter('#list_sites span[class!=left]');
                    if ($sitesNode->count() > 0)
                    {
                        $sites = $sitesNode->each(function($node, $i)
                        {
                            return $node->text();
                        });
                    }
                    // dd($sites);

                    // list_email
                    $emailsNode = $crawler->filter('#list_email a');
                    if ($emailsNode->count() > 0)
                    {
                        $emails = $emailsNode->each(function($node, $i)
                        {
                            return $node->text();
                        });
                    }
                    // dd($emails);

                    // list_description
                    $descriptionNode = $crawler->filter('#list_description #description');
                    if ($descriptionNode->count() > 0) $description = $descriptionNode->text();
                    // dd($description);

                    // list_production
                    $productionNode = $crawler->filter('#list_production #production');
                    if ($productionNode->count() > 0) $production = $productionNode->text();
                    // dd($production);

                    // <meta itemprop="latitude" content="43.235317" />
                    $latNode = $crawler->filter('meta[itemprop=latitude]');
                    if ($latNode->count() > 0) $lat = $latNode->extract('content');
                    // dd($lat);

                    // <meta itemprop="longitude" content="76.842958" />
                    $lngNode = $crawler->filter('meta[itemprop=longitude]');
                    if ($lngNode->count() > 0) $lng = $lngNode->extract('content');
                    // dd($lng);
                }
                else
                {
                    $this->error("Error: " . $url->id);
                    continue;
                }

                // $org = [
                //     'url' => $url->url,
                //     'categories' => $categories,
                //     'address' => $address,
                //     'workinghours' => $workingHours,
                //     'contacts' => $contacts,
                //     'sites' => $sites,
                //     'emails' => $emails,
                //     'description' => $description,
                //     'production' => $production,
                //     'lat' => $lat,
                //     'lng' => $lng
                // ];

                $org = [
                    'url' => json_encode(['city' => $whichCity, 'url' => $url->url]),
                    'categories' => json_encode($categories),
                    'address' => json_encode(str_replace("'", '"', $address)),
                    'workinghours' => $workingHours,
                    'contacts' => json_encode($contacts),
                    'sites' => json_encode($sites),
                    'emails' => json_encode($emails),
                    'description' => str_replace("'", '"', $description),
                    'production' => str_replace("'", '"', $production),
                    'lat' => json_encode($lat),
                    'lng' => json_encode($lng),
                    'name' => str_replace("'", '"', $name)
                ];

                $orgs[] = $org;
            }

            if (count($orgs) > 0)
            {
                $query = 'INSERT INTO `parsedobjects` (`url`, `categories`, `address`, `workinghours`, `contacts`, `sites`, `emails`, `description`, `production`, `lat`, `lng`, `created_at`, `updated_at`, `name`) VALUES ';

                $length = count($orgs);
                foreach ($orgs as $key => $org)
                {
                    $query .= "('" . $org['url'] . "','" . $org['categories'] . "','" . $org['address'] . "','" . $org['workinghours'] . "','" . $org['contacts'] . "','" . $org['sites'] . "','" . $org['emails'] . "','" . $org['description'] . "','" . $org['production'] . "','" . $org['lat'] . "','" . $org['lng'] . "', NULL, NULL, '" . $org['name'] . "')";
                
                    if ($key + 1 < $length) $query .= ',';
                }

                // File::put(public_path() . '/data/test.txt', $query);
                // return;

                DB::insert($query);
            }
            else
            {
                $this->info("other cities");
            }
        });    

        $this->info("DONE.");
    }

    private function parseXml()
    {
        $xml = XmlParser::load(public_path() . '/data/sitemap.xml');

        $url = $xml->parse([
            'loc' => ['uses' => 'url.loc'],
            'lastmod' => ['uses' => 'url.lastmod']
        ]);

        // dd($url);

        $urls = $xml->parse([
            'urls' => ['uses' => 'url[loc,lastmod]'],
        ]);

        foreach (array_chunk($urls, 100) as $chunk)
        {
            foreach ($chunk as $data)
            {
                foreach ($data as $item)
                {
                    // dd($item);
                    
                    DB::table('urls')->insert([
                        'url' => $item['loc'],
                        'lastmod' => $item['lastmod']
                    ]);

                    $this->info('.');
                }
            }
        }

        $this->info("DONE.");
    }

    private function getOnlyName()
    {
        $limit = $this->argument('limit');

        DB::table('parsedobjects')->where("name", "")->chunk($limit, function($objects) use ($limit, &$lastId)
        {
            $ids = [];
            $names = [];

            foreach ($objects as $key => $object) 
            {
                $field = json_decode($object->url);
                $city = $field->city;

                $url = $field->url;
                // dd($url);

                $proxies = [];

                $handle = fopen(public_path() . "/data/proxies.txt", "r");
                if ($handle) 
                {
                    while (($line = fgets($handle)) !== false) 
                    {
                        $proxies[] = trim($line);
                    }

                    fclose($handle);
                }

                $proxy = $proxies[array_rand($proxies)];
                // dd($proxy);

                $client = new Client();
                $guzzle = $client->getClient(["proxy" => $proxy]);
                $client->setClient($guzzle);

                // $url = new StdClass;
                // $url->id = 1;
                // $url->url = "http://orgpage.kz/otegen-batyir/almatysystem-2653039.html";
                // $url->url = "http://orgpage.kz/astana/uk-shanyrak-2564149.html";
                
                $crawler = $client->request('GET', $url);       
                $status_code = $client->getResponse()->getStatus();

                if ($status_code == 200)
                {
                    $nameNode = $crawler->filter('h1.profile[itemprop=name]');
                    // dd($nameNode->text());
                    
                    // DB::table('parsedobjects')->where('id', $object->id)->update(['name' => $nameNode->text()]);

                    $ids[] = $object->id;
                    $names[] = ($nameNode->count() > 0) ? $nameNode->text() : "БЕЗ НАЗВАНИЯ";
                }
                else
                {
                    $this->error("URL error: " . $url);
                }
            }

            if (count($ids) > 0 && count($names) > 0)
            {
                $query =    'UPDATE parsedobjects SET name = CASE id';
                $whereQuery = ' WHERE id IN (';

                $length = count($ids);
                foreach ($ids as $key => $id)
                {
                    $query .= " WHEN " . $id . " THEN '" . $names[$key] . "'";
                    $whereQuery .= $id;

                    if ($key + 1 < $length) $whereQuery .= ", ";
                }
                        
                $query .= ' ELSE "" END';
                $whereQuery .= ')';

                // dd($query . $whereQuery);
                DB::update($query . $whereQuery);
            }
        });

        $this->info("DONE");
    }

    private function getOrgIds()
    {
      $count = DB::table('organizations')->where('notes', '!=', '')->where('id', '>', 137839)->count();
      $bar = $this->output->createProgressBar($count);

      DB::table('organizations')
            ->where('notes', '!=', '')
            ->where('id', '>', 137839)
            ->chunk(1000, function($orgs) use (&$bar)
      {
            foreach ($orgs as $org)
            {
                  File::append(public_path() . '/data/org_astana.txt', $org->notes . "\n");
                  $bar->advance();
            }
      });

      $bar->finish();
    }


    private function getBranches()
    {
      // $file = File::get(public_path() . '/data/org_ids.txt');
      // // dd($file);
      $data = file(public_path() . '/data/org_ids.txt');
      // dd($data);

      $count = count($data);
      $bar = $this->output->createProgressBar($count);
      
      foreach(array_chunk($data, 1000) as $chunk)
      {
            foreach ($chunk as $orgId)
            {
                  $orgId = trim($orgId);
                  $proxy = $this->getRandomProxy();

                    $path = public_path() . "/data/gis-branches/";
                    $url = "https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=1&page_size=12&org_id=" . $orgId . "&hash=f6275edd97161405&stat%5Bpr%5D=8&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Chash%2Csearch_attributes&key=ruczoy1743";

                    $opts = array(
                        'https' => array(
                            'method'=>"GET",
                            'proxy' => $proxy,
                        ),
                        "ssl" => array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        )
                    );
                    $context = stream_context_create($opts);

                    $data = file_get_contents($url, false, $context);

                  $data = json_decode($data);
                  // dd($data);

                  if ($data->meta->code == 404) 
                  {
                      $this->info("no results for " . $rubric['id']);
                      continue;
                  }

                  $total = $data->result->total;
                  $numOfPages = intval(ceil($total / 50.0));

                  if ($numOfPages == 1)
                  {
                      $this->downloadBranches($orgId, 1);
                  }
                  else
                  {
                      for ($i = 1; $i <= $numOfPages; $i++) 
                      {
                          $this->downloadBranches($orgId, $i);
                      }
                  }


                  $bar->advance();
            }
            
      }

      $bar->finish();
    }

    private function downloadBranches($orgId, $pageNum)
    {
      $proxy = $this->getRandomProxy();

        $path = public_path() . "/data/gis-branches/";
        $url = "https://catalog.api.2gis.ru/2.0/catalog/branch/list?page=" . $pageNum . "&page_size=50&org_id=" . $orgId . "&hash=f6275edd97161405&stat%5Bpr%5D=8&fields=items.region_id%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.schedule%2Citems.org%2Citems.ads.options%2Citems.reg_bc_url%2Crequest_type%2Cwidgets%2Cfilters%2Citems.reviews%2Chash%2Csearch_attributes&key=ruczoy1743";

        $opts = array(
            'https' => array(
                'method'=>"GET",
                'proxy' => $proxy,
            ),
            "ssl" => array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        );
        $context = stream_context_create($opts);

        $data = file_get_contents($url, false, $context);
        file_put_contents($path . $orgId . "-" . $pageNum . ".json", $data);
    }


    private function decodeUnicode($str)
    {
        return json_decode('"' . str_replace('u', '\u', $str . '"'));
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
