<?php
class JSMin {
    static public function minify($content) {
        $file    = '/tmp/'.microtime(true);
        file_put_contents($file, $content);
        $jar     = __DIR__.'/jsCompiler.jar';
        $shell   = "java -jar {$jar} --js={$file}";
        return shell_exec($shell);
    }
    
    static public function minifyFiles($files)
    {
        $jar     = __DIR__.'/jsCompiler.jar';
        $shell   = "java -jar {$jar} --js=".implode(' --js=', $files);
        return shell_exec($shell);
    }
}
