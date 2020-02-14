<?php


foreach ($this->footer_js as$idx=>  $arr) {

    $file=$arr[0];
    $type=$arr[1];
    $data=$arr[2];
    $dependencies = $arr[3]??false;
    if($dependencies == false) {
        echo"\r\n<!-- Footer Script Injection #$idx  [$file] -12- -->\r\n";
        echo sprintf('<script src="%s?%s" %s %s></script>',
            $file,
            microtime(true),
            $type == false ? '' : "type='$type'",
            $data != false ? sprintf("data-data='%s'", str_replace("'","&rsquo;",json_encode($data))) : ''
        );
    }else{
        echo"\r\n<!-- Footer Script Embed #$idx  [$file] -20- -->\r\n";
        $script=[];
        foreach($dependencies as $scriptFile){
            $script[]=file_get_contents(ROOT.$scriptFile);
        }
        $script[]=file_get_contents(ROOT.$file);
        $script=implode("\r\n\r\n",$script);
        echo sprintf('<script  %s %s>%s</script>',
            $type == false ? '' : "type='$type'",
            $data != false ? sprintf("data-data='%s'", str_replace("'","&rsquo;",json_encode($data))) : '',
                $script
        );
        echo"\r\n<!-- END Footer Script Embed  [$file]-->\r\n";
    }
}


foreach ($this->footer_js_code as $code) {

    echo $code;
}