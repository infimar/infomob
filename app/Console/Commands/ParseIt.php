<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use XmlParser;
use DB;
use StdClass;
use File;
use App\Category;
use App\Organization;
use App\Branch;
use App\Phone;
use App\Social;

class ParseIt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:do {limit}';

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return;
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
                    $orgDescription = $object->description . "/n" . $object->production;
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
