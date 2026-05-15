<?php 
// Include configuration file for base URLs
require_once __DIR__ . '/config.php';
?>
<script>
  const baseUrl = `<?php echo $Base_Url ?>`;
</script>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/svg+xml" href="<?php echo $Base_Url; ?>/assets/images/icons/vite.svg" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?php echo $Base_Url; ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?php echo $Base_Url; ?>/assets/css/responsive.css">
  <link href="<?php echo $Base_Url; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo $Base_Url; ?>/assets/css/owl.carousel.min.css" rel="stylesheet">
  <link href="<?php echo $Base_Url; ?>/assets/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet" />
  <script defer type="module" src="<?php echo $Base_Url; ?>/assets/js/jquery.js"></script>
  <script defer type="module" src="<?php echo $Base_Url; ?>/assets/js/jqueryValidate.js"></script>
  <script defer src="<?php echo $Base_Url; ?>/assets/js/owl.carousel.min.js"></script>
  <script src="<?php echo $Base_Url; ?>/assets/js/toast.js"></script>


  <title>Ecomm website</title>
</head>

<?php // Database connection is already included via config.php ?>

<body>