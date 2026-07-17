<?php snippet('head') ?>
<main class="home-hero">
  <h1><?= $page->title() ?></h1>
  <?php
    // Core snippet from the vendored Codey plugin (overridable by name).
    snippet('codey/card', ['title' => 'Hello', 'text' => 'From the Codey core.']);
  ?>
</main>
