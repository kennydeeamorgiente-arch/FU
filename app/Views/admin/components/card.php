<div class="medium-card <?= esc($color ?? 'red') ?>">
  <div class="title ">
    <h3><?= esc($title ?? "hello") ?></h3>
  </div>

  <div class="card-content">
    <h1><?= esc($content ?? " ") ?></h1>
  </div>
  <div class="card-footer">
    <h5><?= esc($footer ?? "") ?></h5>
  </div>
</div>