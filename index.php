<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');

if (isset($_SESSION['last_signin_time']) && (time() - $_SESSION['last_signin_time']) > 10000) {
    header("Location:signout.php");
    exit();
}

$_SESSION['last_signin_time'] = time();
include 'header.php';
?>
<main>
  <section class="home-first-section position-relative">
    <div class="container">
      <div class="row flex-column-reverse flex-lg-row">
        <div class="col-12 col-lg-6">
          <div class="hero-content">
            <p class="title">Explore the Latest in Tech Industries</p>
            <h1 class="section-title-h1">
              Your Destination for Cutting-Edge Gadgets!
            </h1>
            <p class="text-white">
              Welcome to GadgetHub, your ultimate destination for
              cutting-edge gadgets! Explore the latest in tech innovation and
              style with us. Shop now and discover a world of possibilities!
            </p>
            <div class="hero-btn mt-5 mb-5">
              <a href="#"> Explore Our Products </a>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <img src="<?php echo $Base_Url; ?>/assets/images/resources/heroImage.png" alt="hero image"
            class="img-fluid" />
        </div>
      </div>
    </div>
    <div class="custom-shape-divider-bottom-1752644272">
      <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path
          d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
          opacity=".25" class="shape-fill"></path>
        <path
          d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
          opacity=".5" class="shape-fill"></path>
        <path
          d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"
          class="shape-fill"></path>
      </svg>
    </div>
  </section>
</main>

<?php include 'product-content.php';?>
<section class="home-fifth-section my-5 pt-2 pt-md-4">
  <div class="container ">
    <h2 class="section-title-h2 text-center mt-3">Why Choose Us</h2>
    <p class="fw-semibold text-gray text-center mb-5">
      Choose GadgetHub for genuine products, fair prices and support that
      stays with you after checkout.
    </p>
    <div class="row">
      <div class="col-xxl-4 col-lg-12 col-md-6 col-12">
        <div class="titled-para">
          <div class="d-flex align-items-start align-items-xxl-end justify-content-center flex-column">
            <p class="number-highlight mb-3">1</p>
            <h3 class="section-title-h3">Wide Selection</h3>
          </div>
          <p class="">
            GadgetHub offers a diverse range of gadgets, from smartphones
            to smart home devices, ensuring you find what you need to
            elevate your lifestyle and meet your tech requirements.
          </p>
        </div>
        <div class="titled-para">
          <div class="d-flex align-items-start align-items-xxl-end justify-content-center flex-column">
            <p class="number-highlight mb-3">2</p>
            <h3 class="section-title-h3">Quality Assurance</h3>
          </div>
          <p class="">
            Every gadget at GadgetHub undergoes rigorous quality checks,
            guaranteeing reliability and performance, so you can shop with
            confidence knowing you're getting the best.
          </p>
        </div>
        <div class="titled-para mb-lg-5 pb-lg-3 mb-xxl-0 pb-xxl-0">
          <div class="d-flex align-items-start align-items-xxl-end justify-content-center flex-column">
            <p class="number-highlight mb-3">3</p>
            <h3 class="section-title-h3">Competitive Prices</h3>
          </div>
          <p class="">
            Enjoy great value with GadgetHub's competitive prices on
            high-quality gadgets, making top-of-the-line technology
            accessible to all without compromising on quality or
            performance.
          </p>
        </div>
      </div>
      <div class="col-xxl-4 col-lg-12 col-md-6 col-12 d-none d-lg-flex align-items-center justify-content-center">
        <div class="img-box position-relative z-2">
          <img src="<?php echo $Base_Url; ?>/assets/images/resources/ecompost.png" alt="" class="img-fluid" />
        </div>
      </div>
      <div class="col-xxl-4 col-lg-12 col-md-6 col-12">
        <div class="titled-para mt-lg-5 pt-lg-3 mt-xxl-0 pt-xxl-0">
          <div class="d-flex align-items-start justify-content-center flex-column">
            <p class="number-highlight mb-3">4</p>
            <h3 class="section-title-h3">Wide Selection</h3>
          </div>
          <p class="">
            GadgetHub offers a diverse range of gadgets, from smartphones
            to smart home devices, ensuring you find what you need to
            elevate your lifestyle and meet your tech requirements.
          </p>
        </div>
        <div class="titled-para">
          <div class="d-flex align-items-start justify-content-center flex-column">
            <p class="number-highlight mb-3">5</p>
            <h3 class="section-title-h3">Quality Assurance</h3>
          </div>
          <p class="">
            Every gadget at GadgetHub undergoes rigorous quality checks,
            guaranteeing reliability and performance, so you can shop with
            confidence knowing you're getting the best.
          </p>
        </div>
        <div class="titled-para">
          <div class="d-flex align-items-start justify-content-center flex-column">
            <p class="number-highlight mb-3">6</p>
            <h3 class="section-title-h3">Competitive Prices</h3>
          </div>
          <p class="">
            Enjoy great value with GadgetHub's competitive prices on
            high-quality gadgets, making top-of-the-line technology
            accessible to all without compromising on quality or
            performance.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'footer.php';?>
<script defer type="module">
$('.featured-items').owlCarousel({
  loop: true,
  margin: 10,
  autoplay: true,
  autoplayTimeout: 3000,
  autoplayHoverPause: true,
  responsiveClass: true,
  responsive: {
    0: {
      items: 1,
    },
    600: {
      items: 2,
    },
    1000: {
      items: 2,
    },
    1200: {
      items: 3,
      loop: false,
      mouseDrag: false,
    }
  }
});
</script>
<?php include 'main-footer.php';?>