<?php

function flashMessages() {
    if ( isset($_SESSION['error']) ) {
        echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
        unset($_SESSION['error']);
    }
    if ( isset($_SESSION['success']) ) {
        echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
        unset($_SESSION['success']);
    }
}

function dumpTable($stmt, $view=false) {
    if ( $view !== false ) {
        if ( strpos($view, '?') !== false ) {
            $view .= '&';
        } else {
            $view .= '?';
        }
    }
    echo('<table border="1">');
    $first = true;
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        if ( $first ) {
            echo("\n<tr>\n");
            foreach($row as $k => $v ) {
                if ( $view !== false && strpos($k, "_id") !== false && is_numeric($v) ) {
                    continue;
                }
                echo("<th>".htmlent_utf8($k)."</th>\n");
            }
            echo("</tr>\n");
        }
        $first = false;

        $link_name = false;
        echo("\n<tr>\n");
        foreach($row as $k => $v ) {
            if ( $view !== false && strpos($k, "_id") !== false && is_numeric($v) ) {
                $link_name = $k;
                $link_val = $v;
                continue;
            }
            echo("<td>");
            if ( $link_name !== false ) {
                echo('<a href="'.$view.$link_name."=".$link_val.'">');
                if ( strlen($v) < 1 ) $v = $link_name.':'.$link_val;
            }
            echo(htmlent_utf8($v));
            if ( $link_name !== false ) {
                echo('</a>');
            }
            $link_name = false;
            echo("</td>\n");
        }
        echo("</tr>\n");
    }
    echo("</table>\n");
}

function doCSS($context=false) {
    global $CFG;
    echo '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/static/default.css" />'."\n";
    if ( $context !== false ) {
        foreach ( $context->getCSS() as $css ) {
            echo '<link rel="stylesheet" type="text/css" href="'.$css.'" />'."\n";
        }
    }
}

function headerContent($headCSS=false) {
    global $HEAD_CONTENT_SENT, $CFG, $RUNNING_IN_TOOL;
	global $CFG;
    if ( $HEAD_CONTENT_SENT === true ) return;
    header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo($CFG->servicename); ?></title>
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="<?php echo($CFG->wwwroot); ?>/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo($CFG->wwwroot); ?>/favicon_io/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo($CFG->wwwroot); ?>/favicon_io/favicon-32x32.png">
    <link rel="apple-touch-icon" href="<?php echo($CFG->wwwroot); ?>/favicon_io/apple-touch-icon.png">
    <link rel="manifest" href="<?php echo($CFG->wwwroot); ?>/favicon_io/site.webmanifest">
    <!-- Le styles -->
    <link href="<?php echo($CFG->staticroot); ?>/static/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo($CFG->staticroot); ?>/static/bootstrap-theme.min.css" rel="stylesheet">

<style> <!-- from navbar.css -->
body {
  padding-top: 20px;
  padding-bottom: 20px;
}

.navbar {
  margin-bottom: 20px;
}

/* Show/hide navigation based on screen size */
@media (max-width: 767px) {
  .navbar-nav.nav-wide {
    display: none !important;
  }
  /* Ensure collapse container is visible when expanded */
  .navbar-collapse.collapse.in,
  .navbar-collapse.collapse.collapsing {
    display: block !important;
  }
  /* Force nav-narrow to be visible when collapse is expanded */
  .navbar-collapse.in .navbar-nav.nav-narrow,
  .navbar-collapse.collapsing .navbar-nav.nav-narrow {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
  }
  .navbar-nav.nav-narrow {
    text-align: right;
    margin: 7.5px -15px !important;
  }
  /* Ensure list items are visible when parent collapse is expanded */
  .navbar-collapse.in .navbar-nav.nav-narrow > li,
  .navbar-collapse.collapsing .navbar-nav.nav-narrow > li {
    display: list-item !important;
    text-align: right;
    float: none !important;
    visibility: visible !important;
    opacity: 1 !important;
  }
  /* Ensure links are visible */
  .navbar-collapse.in .navbar-nav.nav-narrow > li > a,
  .navbar-collapse.collapsing .navbar-nav.nav-narrow > li > a {
    padding-right: 15px;
    padding-top: 10px;
    padding-bottom: 10px;
    text-align: right;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
  }
  /* Ensure divider is visible */
  .navbar-collapse.in .navbar-nav.nav-narrow > li.divider,
  .navbar-collapse.collapsing .navbar-nav.nav-narrow > li.divider {
    margin-right: 15px;
    margin-left: 15px;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
  }
}

@media (min-width: 768px) {
  .navbar-nav.nav-narrow {
    display: none !important;
  }
  .navbar-nav.nav-wide {
    display: block !important;
  }
}
</style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="<?php echo($CFG->wwwroot); ?>/static/html5shiv.js"></script>
      <script src="<?php echo($CFG->wwwroot); ?>/static/respond.min.js"></script>
    <![endif]-->

<?php
    $HEAD_CONTENT_SENT = true;
}

function startBody() {
    echo("\n</head>\n<body style=\"padding: 15px 15px 15px 15px;\">\n");
    if ( count($_POST) > 0 ) {
        $dump = var_dump($_POST);
        echo('<p style="color:red">Error - Unhandled POST request</p>');
        echo("\n<pre>\n");
        echo($dump);
        echo("\n</pre>\n");
        error_log("Unhandled POST request");
        error_log($dump);
        die();
    }
}
function footerStart() {
    global $CFG;
    // jQuery and Bootstrap JS removed - using vanilla JavaScript instead
	do_analytics(); 
}

function footerEnd() {
    echo("\n</body>\n</html>\n");
}

function footerContent($onload=false) {
    global $CFG;
    footerStart();
    if ( $onload !== false ) {
        echo("\n".$onload."\n");
    }
    footerEnd();
}

function do_analytics() {
    global $CFG;
    if ( $CFG->analytics_key ) { ?>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo($CFG->analytics_key); ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php
    }  // if analytics is on...
}

function dumpPost() {
        print "<pre>\n";
        print "Raw POST Parameters:\n\n";
        ksort($_POST);
        foreach($_POST as $key => $value ) {
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) $value = stripslashes($value);
            print "$key=$value (".mb_detect_encoding($value).")\n";
        }
        print "</pre>";
}

function json_indent($json) {
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    $json = str_replace('\/', '/',$json);
    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
    return $result;
}

function header_json() {
    header('Content-type: application/json');
}

function json_error($message,$detail="") {
    header('Content-Type: application/json; charset=utf-8');
    echo(json_encode(array("error" => $message, "detail" => $detail)));
}

function json_output($json_data) {
    header('Content-Type: application/json; charset=utf-8');
    echo(json_encode($json_data));
}

function cacheCheck($cacheloc, $cachekey)
{
    $cacheloc = "cache_" . $cacheloc;
    if ( isset($_SESSION[$cacheloc]) ) {
        $cache_row = $_SESSION[$cacheloc];
        if ( $cache_row[0] == $cachekey ) {
            // error_log("Cache hit $cacheloc");
            return $cache_row[1];
        }
        unset($_SESSION[$cacheloc]);
    }
    return false;
}

// Don't cache the non-existence of something
function cacheSet($cacheloc, $cachekey, $cacheval)
{
    $cacheloc = "cache_" . $cacheloc;
    if ( $cacheval === null || $cacheval === false ) {
        unset($_SESSION[$cacheloc]);
        return;
    }
    $_SESSION[$cacheloc] = array($cachekey, $cacheval);
}

function cacheClear($cacheloc)
{
    $cacheloc = "cache_" . $cacheloc;
    if ( isset($_SESSION[$cacheloc]) ) {
        // error_log("Cache clear $cacheloc");
    }
    unset($_SESSION[$cacheloc]);
}

// Using PHP's built-in OpenSSL encryption instead of custom AES classes
// AES-256-CBC encryption with random IV for secure cookies

function create_secure_cookie($id,$guid,$debug=false) {
    global $CFG;
    $pt = $CFG->cookiepad.'::'.$id.'::'.$guid;
    if ( $debug ) echo("PT1: $pt\n");
    
    // Generate a random IV (initialization vector) for each encryption
    $iv_length = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    // Derive a 32-byte key from the secret using SHA-256
    $key = hash('sha256', $CFG->cookiesecret, true);
    
    // Encrypt the plaintext
    $encrypted = openssl_encrypt($pt, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    
    // Prepend IV to encrypted data and base64 encode for safe storage
    $ct = base64_encode($iv . $encrypted);
    
    return $ct;
}

function extract_secure_cookie($encr,$debug=false) {
    global $CFG;
    
    // Decode from base64
    $data = base64_decode($encr, true);
    if ($data === false) {
        if ( $debug ) echo("PT2: Invalid base64\n");
        return false;
    }
    
    // Extract IV (first 16 bytes for AES-256-CBC)
    $iv_length = openssl_cipher_iv_length('AES-256-CBC');
    if (strlen($data) < $iv_length) {
        if ( $debug ) echo("PT2: Data too short\n");
        return false;
    }
    
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    
    // Derive the same key from the secret
    $key = hash('sha256', $CFG->cookiesecret, true);
    
    // Decrypt
    $pt = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    
    if ($pt === false) {
        if ( $debug ) echo("PT2: Decryption failed\n");
        return false;
    }
    
    if ( $debug ) echo("PT2: $pt\n");
    $pieces = explode('::',$pt);
    if ( count($pieces) != 3 ) return false;
    if ( $pieces[0] != $CFG->cookiepad ) return false;
    return Array($pieces[1], $pieces[2]);
}

// We also session_unset - because something is not right
// See: http://php.net/manual/en/function.setcookie.php
function delete_secure_cookie() {
    global $CFG;
    setcookie($CFG->cookiename,'',time() - 100); // Expire 100 seconds ago
    session_unset();
}

function test_secure_cookie() {
    $id = 1;
    $guid = 'xyzzy';
    $ct = create_secure_cookie($id,$guid,true);
    echo($ct."\n");
    $pieces = extract_secure_cookie($ct,true);
    if ( $pieces === false ) echo("PARSE FAILURE\n");
    var_dump($pieces);
    if ( $pieces[0] == $id && $pieces[1] == $guid ) {
        echo("Success\n");
    } else {
        echo("FAILURE\n");
    }
}

// test_secure_cookie();

function do_form($values, $override=Array()) {
    foreach (array_merge($values,$override) as $key => $value) {
        if ( $value === false ) continue;
        if ( is_string($value) && strlen($value) < 1 ) continue;
        if ( is_int($value) && $value === 0 ) continue;
        echo('<input type="hidden" name="'.htmlent_utf8($key).
             '" value="'.htmlent_utf8($value).'">'."\n");
    }
}

function do_url($values, $override=Array()) {
    $retval = '';
    foreach (array_merge($values,$override) as $key => $value) {
        if ( $value === false ) continue;
        if ( is_string($value) && strlen($value) < 1 ) continue;
        if ( is_int($value) && $value === 0 ) continue;
        if ( strlen($retval) > 0 ) $retval .= '&';
        $retval .= urlencode($key) . "=" . urlencode($value);
    }
    return $retval;
}

// Function to lookup and match things like R.updated_at to updated_at
function matchColumns($colname, $columns) {
    foreach ($columns as $v) {
        if ( $colname == $v ) return true;
        if ( strlen($v) < 2 ) continue;
        if ( substr($v,1,1) != '.' ) continue;
        if ( substr($v,2) == $colname ) return true;
    }
    return false;
}

$DEFAULT_PAGE_LENGTH = 20;  // Setting this to 2 is good for debugging

// Requires the keyword WHERE to be upper case - if a query has more than one WHERE clause
// they should all be lower case except the one where the LIKE clauses will be added.

// We will add the ORDER BY clause at the end using the first field in $orderfields
// is there is not a 'order_by' in $params

// Normally $params should just default to $_GET
function pagedPDOQuery($sql, &$queryvalues, $searchfields=array(), $orderfields=false, $params=false) {
    global $DEFAULT_PAGE_LENGTH;
    if ( $params == false ) $params = $_GET;
    if ( $orderfields == false ) $orderfields = $searchfields;

    $searchtext = '';
    if ( count($searchfields) > 0 && isset($params['search_text']) ) {
        for($i=0; $i < count($searchfields); $i++ ) {
            if ( $i > 0 ) $searchtext .= " OR ";
            $searchtext .= $searchfields[$i]." LIKE :SEARCH".$i;
            $queryvalues[':SEARCH'.$i] = '%'.$params['search_text'].'%';
        }
    }

    $ordertext = '';
    if ( isset($params['order_by']) && matchColumns($params['order_by'], $orderfields) ) { 
        $ordertext = $params['order_by']." ";
        if ( isset($params['desc']) && $params['desc'] == 1) {
            $ordertext .= "DESC ";
        }
    } else if ( count($orderfields) > 0 ) {
        $ordertext = $orderfields[0]." ";
    }

    $page_start = isset($params['page_start']) ? $params['page_start']+0 : 0;
    if ( $page_start < 0 ) $page_start = 0;
    $page_length = isset($params['page_length']) ? $params['page_length']+0 : $DEFAULT_PAGE_LENGTH;
    if ( $page_length < 0 ) $page_length = 0;

    $desc = '';
    if ( isset($params['desc']) ) { 
        $desc = $params['desc']+0;
    }

    $limittext = '';
    if ( $page_start < 1 ) {
        $limittext = "".($page_length+1);
    } else {
        $limittext = "".$page_start.", ".($page_length+1);
    }

    // Fix up the SQL Query
    $newsql = $sql;
    if ( strlen($searchtext) > 0 ) {
        $newsql = str_replace("WHERE", "WHERE ( ".$searchtext." ) AND ", $newsql);
    }
    if ( strlen($ordertext) > 0 ) {
        $newsql .= "\nORDER BY ".$ordertext." ";
    }
    if ( strlen($limittext) > 0 ) {
        $newsql .= "\nLIMIT ".$limittext." ";
    }
    return $newsql . "\n";
}

function pagedPDOTable($pdo, $sql, &$queryvalues, $searchfields=array(), $orderfields=false, $view=false, $params=false) {
    global $DEFAULT_PAGE_LENGTH;
    if ( $params === false ) $params = $_GET;
    if ( $orderfields === false ) $orderfields = $searchfields;

    $page_start = isset($params['page_start']) ? $params['page_start']+0 : 0;
    if ( $page_start < 0 ) $page_start = 0;
    $page_length = isset($params['page_length']) ? $params['page_length']+0 : $DEFAULT_PAGE_LENGTH;
    if ( $page_length < 0 ) $page_length = 0;

    $search = '';
    if ( isset($params['search_text']) ) {
        $search = $params['search_text'];
    }

    $stmt = pdoQueryDie($pdo, $sql, $queryvalues);

    $rows = array();
    $count = 0;
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        array_push($rows, $row);
        $count = $count + 1;
    }

    $have_more = false;
    if ( $count > $page_length ) {
        $have_more = true;
        $count = $page_length;
    }

    echo('<div style="float:right">');
    if ( $page_start > 0 ) {
        echo('<form style="display: inline">');
        echo('<input type="submit" value="Back">');
        $page_back = $page_start - $page_length;
        if ( $page_back < 0 ) $page_back = 0;
        do_form($params,Array('page_start' => $page_back));
        echo("</form>\n");
    }
    if ( $have_more ) {
        echo('<form style="display: inline">');
        echo('<input type="submit" value="Next"> ');
        $page_next = $page_start + $page_length;
        do_form($params,Array('page_start' => $page_next));
        echo("</form>\n");
    }
    echo("</div>\n");
    echo('<form>');
    echo('<input type="text" id="paged_search_box" value="'.htmlent_utf8($search).'" name="search_text">');
    do_form($params,Array('search_text' => false, 'page_start' => false));
?>
<input type="submit" value="Search">
<input type="submit" value="Clear Search" 
onclick="document.getElementById('paged_search_box').value = '';"
>
</form>
<?php
    if ( $count < 1 ) {
        echo("<p>Nothing to display.</p>\n");
        return;
    }
// print_r($orderfields);
// echo("<hr>\n");
// print_r($rows[0]);
?>

<table border="1">
<tr>
<?php

    $first = true;
    $thispage = basename($_SERVER['PHP_SELF']);
    if ( $view === false ) $view = $thispage;
    foreach ( $rows as $row ) {
        $count--;
        if ( $count < 0 ) break;
        if ( $first ) {
            echo("\n<tr>\n");
            $desc = isset($params['desc']) ? $params['desc'] + 0 : 0;
            $order_by = isset($params['order_by']) ? $params['order_by'] : '';
            foreach($row as $k => $v ) {
                if ( strpos($k, "_") === 0 ) continue;
                if ( $view !== false && strpos($k, "_id") !== false && is_numeric($v) ) {
                    continue;
                }

                if ( ! matchColumns($k, $orderfields ) ) {
                    echo("<th>".ucwords(str_replace('_',' ',$k))."</th>\n");
                    continue;
                }

                $override = Array('order_by' => $k, 'desc' => 0, 'page_start' => false);
                $d = $desc;
                $color = "black";
                if ( $k == $order_by || $order_by == '' && $k == 'id' ) {
                    $d = ($desc + 1) % 2;
                    $override['desc'] = $d;
                    $color = $d == 1 ?  'green' : 'red';
                }
                $stuff = do_url($params,$override);
                echo('<th>');
                echo(' <a href="'.$thispage);
                if ( strlen($stuff) > 0 ) {
                    echo("?");
                    echo($stuff);
                }
                echo('" style="color: '.$color.'">');
                echo(ucwords(str_replace('_',' ',$k)));
                echo("</a></th>\n");
            }
            echo("</tr>\n");
        }

        $first = false;
        $link_name = false;
        echo("<tr>\n");
        foreach($row as $k => $v ) {
            if ( strpos($k, "_") === 0 ) continue;
            if ( $view !== false && strpos($k, "_id") !== false && is_numeric($v) ) {
                $link_name = $k;
                $link_val = $v;
                continue;
            }
            echo("<td>");
            if ( $link_name !== false ) {
                echo('<a href="'.$view.'?'.$link_name."=".$link_val.'">');
                if ( strlen($v) < 1 ) $v = $link_name.':'.$link_val;
            }
            echo(htmlent_utf8($v));
            if ( $link_name !== false ) {
                echo('</a>');
            }
            $link_name = false;
            echo("</td>\n");
        }
        echo("</tr>\n");
    }
    echo("</table>\n");
}

function pagedPDO($pdo, $sql, $query_parms, $searchfields, $orderfields=false, $view=false, $params=false) {
    $newsql = pagedPDOQuery($sql, $query_parms, $searchfields, $orderfields, $params);
    // echo("<pre>\n$newsql\n</pre>\n");
    pagedPDOTable($pdo, $newsql, $query_parms, $searchfields, $orderfields, $view, $params);
}

// No trailer
