<?php
/*
 Copyright 2007 James E. Robinson, III (blog.robinsonhouse.com)

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:

 1. Redistributions of source code must retain the above copyright notice,
 this list of conditions and the following disclaimer.

 2. Redistributions in binary form must reproduce the above copyright notice,
 this list of conditions and the following disclaimer in the documentation
 and/or other materials provided with the distribution.

 3. The name of the author may not be used to endorse or promote products
 derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
 WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
 EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

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
