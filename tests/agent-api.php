<?php
// Run: php tests/agent-api.php. No credentials or network requests are used.
if (PHP_SAPI !== 'cli') { exit; }
define('ABSPATH', '/tmp/');
function add_action(...$args) {}
function get_option(...$args) { return array('perplexity' => 'test-key'); }
function wp_json_encode($value) { return json_encode($value); }
function is_wp_error($value) { return false; }
function wp_remote_retrieve_response_code($response) { return 200; }
function wp_remote_retrieve_body($response) { return json_encode($GLOBALS['fixture']); }
function wp_remote_post($url, $args) {
    $body = json_decode($args['body'], true);
    check($url === 'https://api.perplexity.ai/v1/responses', 'Agent endpoint');
    check($args['headers']['Authorization'] === 'Bearer test-key', 'Authorization preserved');
    check($body['preset'] === 'sonar-pro', 'Preset instead of legacy model');
    check($body['instructions'] === 'Editorial instructions', 'Instructions');
    check(json_decode($body['input'], true) === array('keywords'=>'enerji', 'brief'=>'Türkçe açıklama'), 'Input encoding');
    check($body['max_output_tokens'] === 5000 && $body['stream'] === false && $body['background'] === false, 'Synchronous request');
    check($body['tools'][0]['type'] === 'web_search', 'Search enabled');
    check($body['response_format']['json_schema']['schema']['required'] === array('title', 'meta_description', 'html', 'image_prompt', 'image_alt'), 'Article schema');
    check(!isset($body['messages']) && !isset($body['max_tokens']) && !isset($body['model']), 'No legacy fields');
    return array();
}
function check($condition, $message) { if (!$condition) { throw new Exception($message); } }
require dirname(__DIR__) . '/codex-blog.php';
$method = new ReflectionMethod('Codex_AI_Blog', 'perplexity');
$method->setAccessible(true);
function invoke_agent() {
    return $GLOBALS['method']->invoke(null, 'Editorial instructions', 'enerji', 'Türkçe açıklama');
}
$article = json_encode(array('title'=>'Örnek', 'meta_description'=>'Özet', 'html'=>'<p>Makale</p>', 'image_prompt'=>'Landscape', 'image_alt'=>'Manzara'));
$mid = intdiv(strlen($article), 2);
$base = array('status'=>'completed', 'output'=>array(
    array('type'=>'search_results', 'results'=>array(array('url'=>'https://example.org/researched'))),
    array('type'=>'message', 'role'=>'assistant', 'content'=>array(
        array('type'=>'output_text', 'text'=>substr($article, 0, $mid), 'annotations'=>array(array('type'=>'url_citation', 'url'=>'https://example.org/cited'))),
        array('type'=>'output_text', 'text'=>substr($article, $mid)),
    )),
));
$GLOBALS['fixture'] = $base;
$result = invoke_agent();
check($result['text'] === $article && json_decode($result['text'], true)['title'] === 'Örnek', 'Content parts reconstruct valid article JSON');
check($result['sources'] === array('https://example.org/cited'), 'Explicit citations preferred');
echo "PASS article and citations\n";
$GLOBALS['fixture']['output'][1]['content'][0]['annotations'] = null;
check(invoke_agent()['sources'] === array('https://example.org/researched'), 'Search results fallback');
echo "PASS search result sources\n";
foreach (array('failed', 'in_progress', 'queued', 'cancelled', 'requires_action', 'incomplete') as $status) {
    $GLOBALS['fixture'] = $base;
    $GLOBALS['fixture']['status'] = $status;
    try { invoke_agent(); throw new Exception('Unfinished response accepted'); }
    catch (RuntimeException $e) { check(strpos($e->getMessage(), 'tamamlamadı') !== false, 'Status rejected'); }
}
echo "PASS six unfinished statuses rejected\n";
foreach (array(array(), array(array('type'=>'reasoning', 'text'=>'Do not publish this')), array(array('type'=>'message', 'role'=>'assistant', 'content'=>null))) as $output) {
    $GLOBALS['fixture'] = array('status'=>'completed', 'output'=>$output);
    try { invoke_agent(); throw new Exception('Empty text accepted'); }
    catch (RuntimeException $e) { check(strpos($e->getMessage(), 'makale metni bulunamadı') !== false, 'Missing text rejected'); }
}
echo "PASS empty and non-message outputs rejected\n";
