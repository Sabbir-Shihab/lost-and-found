<!-- ======= Header ======= -->
<header id="header" class="header fixed-top">
  <div class="header-topbar">
    <div class="container-xl px-4 d-flex align-items-center justify-content-between gap-3">
      <span class="header-tagline">Uttara University Lost &amp; Found</span>
      <span class="header-support d-none d-md-inline-flex">Campus recovery portal</span>
    </div>
  </div>

  <div class="header-main">
    <div class="container-xl px-4 d-flex align-items-center justify-content-between gap-3 header-shell">
      <a href="<?= base_url ?>" class="logo d-flex align-items-center header-brand">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
        <span class="site-name"><?= $_settings->info('short_name') ?></span>
      </a><!-- End Logo -->

      <button class="navbar-toggler header-toggle d-lg-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#siteTopNav" aria-controls="siteTopNav" aria-expanded="false" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
      </button>

      <div class="collapse navbar-collapse header-collapse w-100 d-lg-flex align-items-center justify-content-between" id="siteTopNav">
        <nav class="header-nav me-lg-auto header-menu-wrap">
          <ul class="d-flex align-items-center h-100 mb-0">
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url ?>" class="nav-link"><i class="bi bi-house-door me-2 d-lg-none"></i>Home</a>
            </li>
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url.'?page=items' ?>" class="nav-link"><i class="bi bi-search me-2 d-lg-none"></i>Lost and Found</a>
            </li>
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url.'?page=rechived' ?>" class="nav-link"><i class="bi bi-inbox me-2 d-lg-none"></i>Rechived</a>
            </li>
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url.'?page=found' ?>" class="nav-link"><i class="bi bi-plus-circle me-2 d-lg-none"></i>Post an Item</a>
            </li>
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url."?page=about" ?>" class="nav-link"><i class="bi bi-info-circle me-2 d-lg-none"></i>About</a>
            </li>
            <li class="nav-item pe-lg-3">
              <a href="<?= base_url.'?page=contact' ?>" class="nav-link"><i class="bi bi-envelope me-2 d-lg-none"></i>Contact Us</a>
            </li>
          </ul>
        </nav><!-- End Icons Navigation -->
      </div>
    </div>
  </div>
</header><!-- End Header -->