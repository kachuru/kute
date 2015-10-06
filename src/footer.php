<?php
if (extension_loaded('xhprof') && $xhprofProfile) {
    if (!isset($profiler_namespace)) {
        $profiler_namespace = $_SERVER['HTTP_HOST'];  // namespace for your application
    }
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf('http://' . $_SERVER['HTTP_HOST'] . '/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
    echo "<a href=\"{$profiler_url}\" target=\"_blank\">Profiler Output</a>";
}
