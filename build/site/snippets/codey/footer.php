<?php
    /** @var \Kirby\Cms\Site $site */
    /**
     * Codey footer (core) — closes <main>, renders <footer>, emits body-tail JS.
     * Generic scaffold using the .grid-12 column system; replace the inner
     * content with the project's. Override by name to customise.
     */
?>
  </main>

  <footer class="footer">
    <div class="grid-12">
      <div class="column" style="--span: 6">
        <p class="logo text-xl decor"><?= $site->title() ?></p>
      </div>
      <div class="column flex justify-end items-end" style="--span: 6">
        <!-- project footer content (contact, nav, social) -->
      </div>
    </div>
  </footer>

  <?= js(['@auto'], ['nonce' => function_exists('cspNonce') ? cspNonce() : null]) ?>
</body>
</html>
