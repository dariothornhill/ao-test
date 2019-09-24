<?php

function reverse($string)
{
  echo strlen($string);
  //Assuming we are not counting the terminator.
  if (strlen($string) >= 5) {
    return strrev($string);
  }
  trigger_error("Please provide a string of at least 5 characters", E_USER_WARNING);
}

try {
  $test = "I am here, looking at you";
  echo reverse($test);
} catch (Exception $e) {
  //In production we would ship to a logger.
}

$sdArray = [4, 10, 8, 34, 35, 12, 1, 9, 8, 14, 28];
$sdArrayCopy = $sdArray;

//Since we don't have to worry about keys asort is okay
asort($sdArray);

print_r($sdArray);

//Question 3
rsort($sdArrayCopy);
print_r(array_unique($sdArrayCopy, SORT_NUMERIC));


//Question 4

function callAPI($method, $url, $data)
{
  $curl = curl_init();

  switch ($method) {
    case "POST":
      curl_setopt($curl, CURLOPT_POST, 1);
      if ($data)
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      break;
    case "PUT":
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
      if ($data)
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      break;
    default:
      if ($data)
        $url = sprintf("%s?%s", $url, http_build_query($data));
  }

  // OPTIONS:
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'APIKEY: 111111111111111111111',
    'Content-Type: application/json',
  ));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

  // EXECUTE:
  $result = curl_exec($curl);
  if (!$result) {
    throw new Exception("Connection Failure");
  }
  curl_close($curl);
  return $result;
}

function fetch($api_url)
{
  try {
    // pretend we did something like this:
    $data = callAPI('GET', $api_url, null);
  } catch (Exception $e) {
    //Let's fake the data
    $data = '[{
      "title": "The Title",
      "url": "http://the.url",
      "img": "http://the.url/img.png",
      "id": 1,
      "alt": "title image"
    }, {
      "title": "The 2nd Title",
      "url": "http://the2nd.url",
      "img": "http://the2nd.url/img.png",
      "id": 1,
      "alt": "title image"
    }]';
  }
  $response = json_decode($data, true);
  return $response;
}

function renderData($data)
{
  var_dump($data);
  foreach ($data as $row) {
    echo "<a href='{$row["url"]}' data-id='{$row['id']}><img src='{$row["img"]}' alt='{$row["alt"]}'/>{$row["title"]}</a>\n";
  }
}

$data = fetch("https://some.url");
renderData($data);


//Question 5

//1.
$numbers = range(2, 20, 2);
print_r($numbers);

//2.
for ($i = 2; $i <= 20; $i += 2) {
  echo $i . "\n";
}

//3.
for ($i = 1; $i <= 20; $i++) {
  if ($i % 2 == 0) {
    echo $i . "\n";
  }
}

//Question 6

class Database
{

  private static $_conn = null;

  private function __constructor()
  {
    die("Not allowed");
  }

  static function connect($dbname = "myDB", $host = "localhost", $user = "user1", $pass = "pass1")
  {
    if (self::$_conn === null) {
      try {
        self::$_conn = new PDO("mysql:host=$host;dbname=$dbname",  $user, $pass);
      } catch (PDOException $e) {
        // Check connection
        die("Connection failed: " . $e->getMessage());
      }
    }
    return self::$_conn;
  }

  static function create($table, $data)
  {
    //get field names
    $keys = array_keys($data);
    $fields = implode(",", $keys);
    //get labels
    $labels = ":" . implode(", :", $keys);
    substr($labels, 0, -1);

    //Build sql
    $sql = sprintf("INSERT into `%s` (%s) values (%s)", $table, $fields, $labels);

    //prepare statement
    return self::$_conn->prepare($sql)->execute($data);
  }

  static function read($table, $where = false, $attributes = '*', $order = false, $limit = false, $offset = false)
  {
    $criteria = array();
    $fields = (is_array($attributes)) ? implode(",", $attributes) : $attributes;

    $sql = sprintf("SELECT %s FROM `%s` where true", $fields, $table);

    if ($where !== false) {
      foreach ($where as $key => $value) {
        $sql .= " AND `{$key}` = :{$key}";
      }
      $criteria = array_merge($criteria, $where);
    }

    if ($order !== false && is_array($order)) {
      $sql .= ' ORDER BY';
      foreach ($order as $field => $mode) {
        $sortOrder[] = " {$field} {($mode == 0) ? ASC : DESC}";
      }
      $sql .= implode(",", $sortOrder);
    }

    if ($limit !== false) {
      $sql .= ' LIMIT :limit';
      $criteria = array_merge($criteria, ["limit" => $limit]);

      if ($offset !== false) {
        $sql .= ', :offset';
        $criteria = array_merge($criteria, ["offset" => $offset]);
      }
    }

    $sql .= ';';
    var_dump($sql);
    $stmt = self::$_conn->prepare($sql);
    $stmt->execute($criteria);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static function update($table, $id, $data)
  {
    foreach ($data as $key => $value) {
      $placeholders[] = "{$key}= {:$key}";
    }

    //Build sql
    $sql = sprintf("UPDATE `%s` SET %s WHERE id = %d", $table, $placeholders, $id);

    //prepare and execute statement
    return self::$_conn->prepare($sql)->execute($data);
  }

  static function delete($table, $id)
  {
    $sql = sprintf("DELETE FROM `%s` WHERE id = %d", $table, $id);
    return self::$_conn->prepare($sql)->execute();
  }

  static function serialCopy()
  {
    $rawdata = self::read('table1', ["id" => 2]);
    var_dump($rawdata);
    $data = serialize($rawdata);
    var_dump($data);
    self::create('table2', ['account' => $data]);
  }
}

//Question 7
Database::connect();
Database::create('table1', array("firstname" => "Dario",  "lastname" => "Thornhill", "email" => "dario.thornhill@gmail.com"));
Database::create('table1', array("firstname" => "Dario",  "lastname" => "Thornhill", "email" => "rayne.itami@gmail.com"));
Database::serialCopy();
print_r(Database::read('table2'));

//Question 8
/*
*  Disjointing a value that is always true to the where clause and commenting the remainder of the statement.
*  ' OR 1 = 1 --
*/

//Question 9

/*
* Typically I would not rely only on securing the form to prevent SQL injection but;input validation preferably with a whitelist should mitigate against sql injection through the form as well as cross site scripting.
*/

//Question 10

//arrange
$numbers = range(1, 100);

$index = rand(0, 99);
array_splice($numbers, $index, 1);

function findMissing($range)
{
  //Let's assume it wasn't intially sorted
  sort($range);
  $lowest = $range[0];
  $xor = 0;
  $newXor = 0;

  for ($i = 0; $i < count($range); $i++) {
    $xor ^= $range[$i];
  }
  $newRange = range($lowest, $lowest + 99);
  for ($i = 0; $i < count($newRange); $i++) {
    $newXor ^= $newRange[$i];
  }
  $missing = $xor ^ $newXor;
  return $missing;
}

print_r($numbers);
echo "missing: " . findMissing($numbers) . "\n";


//Question 11

class ListNode
{
  /* Data to hold */
  public $data;

  /* Link to next node */
  public $next;

  /* Node constructor */
  function __construct($data)
  {
    $this->data = $data;
    $this->next = NULL;
  }

  function readNode()
  {
    return $this->data;
  }
}


class LinkList
{
  /* Link to the first node in the list */
  public $firstNode;

  /* Link to the last node in the list */
  private $lastNode;

  /* Total nodes in the list */
  private $count;

  /* List constructor */
  function __construct()
  {
    $this->firstNode = NULL;
    $this->lastNode = NULL;
    $this->count = 0;
  }

  public function isEmpty()
  {
    return ($this->firstNode == NULL);
  }

  public function insertFirst($data)
  {
    $link = new ListNode($data);
    $link->next = $this->firstNode;
    $this->firstNode = &$link;

    /* If this is the first node inserted in the list
           then set the lastNode pointer to it.
        */
    if ($this->lastNode == NULL)
      $this->lastNode = &$link;

    $this->count++;
  }

  public function insertLast($data)
  {
    if ($this->firstNode != NULL) {
      $link = new ListNode($data);
      $this->lastNode->next = $link;
      $link->next = NULL;
      $this->lastNode = &$link;
      $this->count++;
    } else {
      $this->insertFirst($data);
    }
  }

  public function readNode($nodePos)
  {
    if ($nodePos <= $this->count) {
      $current = $this->firstNode;
      $pos = 1;
      while ($pos != $nodePos) {
        if ($current->next == NULL)
          return null;
        else
          $current = $current->next;

        $pos++;
      }
      return $current->data;
    } else
      return NULL;
  }

  public function readList()
  {
    $listData = array();
    $current = $this->firstNode;

    while ($current != NULL) {
      array_push($listData, $current->readNode());
      $current = $current->next;
    }
    return $listData;
  }
  //Question 11
  public function reverseListRecursive($curr)
  {
    if ($curr == NULL) {
      return;
    }

    if ($curr->next == NULL) {
      $this->firstNode = $curr;
      return;
    }
    $this->reverseListRecursive($curr->next);
    $curr->next->next = $curr;
    $curr->next = null;
  }

  //Question 12
  public function reverseList()
  {
    if ($this->firstNode != NULL) {
      if ($this->firstNode->next != NULL) {
        $current = $this->firstNode;
        $new = NULL;

        while ($current != NULL) {
          $temp = $current->next;
          $current->next = $new;
          $new = $current;
          $current = $temp;
        }
        $this->firstNode = $new;
      }
    }
  }
}

$list = new LinkList();
$list->insertLast(1);
$list->insertLast(3);
$list->insertLast(5);
print_r($list->readList());
$list->reverseList();
print_r($list->readList());
$list->reverseListRecursive($list->firstNode);
print_r($list->readList());




//Question 13
/*
* Encoding is concerned with changing data from one usable format to another usable format, usually for ease of transmission or to work with in a foreign system.
* Encryption is concerned with making data secret so it can only be read by the intended recipient. A secret or key is needed in order to recover the original message.
* Hasing is concerned with verifying a message and can be used to detect tampering with a message. A good has function makes it difficult to find two inputs that result in the same hash, Has hashes that are fo a fixed length and hash no known function for which a hash can be given and input and the original message recovered at the output.
*/

//Question 14
/*
* This question is basically asking how would we share sensitive data over a public channel. The solution is the encrypt the data using a method similar to Diff Hellman where keys can be exchanged in the clear and only the intended recipient is able to decrypt the message. This assumes the channel is not compromised via MITM and that the two node have verified each other identity.
*/

//Question 15
/*
* It depends, normall quicksort is sufficiently fast O(n log n) but in this specific case a radix sort can give a O(n) solution. This is because the number of digits (32) and the possible values b (0,1) are both constant and much smaller than the number of values so O(d(n + b)) reduces to O(n). This solution assumes that memory is not contrainted
*/
