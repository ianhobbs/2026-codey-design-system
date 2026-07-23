<?php
/** Project STARTER — listing intro. Replace with your markup. */
?>
<?php if ($page->intro()->isNotEmpty()): ?>
<header class="intro h1"><?= $page->intro()->kirbytext() ?></header>
<?php endif ?>
