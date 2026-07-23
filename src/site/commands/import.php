<?php 

use Kirby\Uuid\Uuid;

return [
   'description' => 'Import data from a CSV file',
   'args'=>[],
   'command'=> static function ($cli): void {

      $kirby = $cli->kirby();
      $kirby->impersonate('kirby');
      $works = $kirby->page('works');
      $counter= 4;
      $json_d = Data::read( kirby()->root() .'/output.json');



      foreach($json_d as $row) {
         $works->createChild([
            'slug' => $row['page'],
            'isDraft' => false,
            'template' => 'album',
            'content' => [ 
               'headline' => $row['title'],
               'body' => $row['body'],
               'collection' =>$row['artist'],
               'Metadescription' => $row['description'],
               'Ogdescription' => $row['description'],
               'keywords' => $row['keywords'],
               'picasso' => $row['allpics'],
               'myvid' =>[ 
                  ['content'=>[
                     'location' => 'web',
                     'url' => $row['video']
                  ]]],
               'pic' => $row['pic'],
            ]
         ]);

         // $works->changeNum($counter);

         $counter++;
      }

      $cli ->success('csv imported');
   }

];