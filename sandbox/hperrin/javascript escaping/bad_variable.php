<?php

// This value is dangerous.

$bad_var = '"; alert("This page is vulnerable to a XSS attack!");</script><div style="background: red; height: 200px; width: 200px;">This page is vulnerable to a XSS attack!</div><script>'."\0";

?>