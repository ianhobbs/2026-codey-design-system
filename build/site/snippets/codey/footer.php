<?php
    /** @var \Kirby\Cms\Site $site */
    /**
     * Codey footer (core) — closes <main>, renders <footer>, emits body-tail JS.
     * Generic scaffold using the .blocks-grid column system; replace the inner
     * content with the project's. Override by name to customise.
     */
?>
  </main>

  <footer class="footer">
    <div class="blocks-grid">
      <div class="column" style="--columns: 6">
        <p class="logo text-xl heads"><?= $site->title() ?></p>
      </div>
      <div class="column flex justify-end items-end" style="--columns: 6">
        <!-- project footer content (contact, nav, social) -->
      </div>
    </div>
  </footer>

  <?= js(['@auto']) ?>
</body>
</html>
