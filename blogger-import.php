<?php
// path to Zend Gdata library:  http://framework.zend.com/download/gdata
$zpath = './ZendGdata-0.7.0-jer/library';
set_include_path(get_include_path() . PATH_SEPARATOR . $zpath);
require_once 'Zend/Gdata/ClientLogin.php';
require_once 'Zend/Gdata/Blogger.php';

$filename = 'export.xml';
$email = 'yourid@gmail.com';
$passwd = 'password';
# custom domain only -- i never had a chance to test using default domain
$blogname = 'blog.yourdomain.com';
$roller = false; # roller blog import file or no? see template

$svc = 'blogger';
$client = Zend_Gdata_ClientLogin::getHttpClient($email, $passwd, $svc);
$gdataBlog = new Zend_Gdata_Blogger($client);
$gdataBlog->setBlogName($blogname);

$xml = simplexml_load_file($filename);

$i = 0;
foreach ($xml->entry as $entry) {
   if ( $i >= $start and $i < $end) {
      echo "--- processing $i\n";

      $entry->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');

      $text = trim($entry->content);
      $text = str_replace("\n", " ", $text); 
      $entry->content = $text;

      // roller hacks
      if ( $roller ) {
         $entry->content->addAttribute('type', 'html');
         $ts = strtotime($entry->published);
         $entry->published = date(DATE_ATOM, $ts);
      }

      $content = $entry->asXML();
      $response = $gdataBlog->post($content, 'http://' . $blogname .
                                    '/feeds/posts/default');

      print_r($response);

      sleep(10); # relax...note, sleep or be killed, your choice
   } else if ($i == $end) {
      break;
   }
   $i++;
}

exit();
?>
