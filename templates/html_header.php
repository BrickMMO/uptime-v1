<!DOCTYPE html>
<html lang="en">
  <head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?=PAGE_TITLE?> | <?=APP_NAME?> | BrickMMO</title>

        
    <!-- W3 School CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css" />

    <!-- BrickMMO Exceptions -->
    <link rel="stylesheet" href="https://cdn.brickmmo.com/exceptions@1.0.0/w3.css" />
    <link rel="stylesheet" href="https://cdn.brickmmo.com/exceptions@1.0.0/fontawesome.css" />

    <!-- BrickMMO Icons -->
    <link rel="stylesheet" href="https://cdn.brickmmo.com/glyphs@1.0.0/icons.css" />

    <!--
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 
    -->
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Script JavaScript File -->
    <script src="/script.js"></script>

    <?php if(!isset($_SESSION['timezone'])): ?>

      <script>

      let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

      let now = new Date();
      let offset = now.getTimezoneOffset();

      async function setTimezone() {
        const response = await fetch('/ajax/timezone/'+offset+'/'+timezone);
      }

      setTimezone();

      </script>

    <?php endif; ?>

  </head>
  <body>

  <div id="loading-overlay" class="w3-modal" style="z-index: 300; display; transition: 0.5s">
    <div class="w3-black w3-text-white w3-padding-16 w3-center" style="max-width: 200px; margin: auto">
      <strong>
        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
        Loading...
      </strong>
    </div>
  </div>

  <script>

    function loading()
    {
      let loadingOverlay = document.getElementById("loading-overlay");  
      loadingOverlay.style.display = "block";

      setTimeout(function () {
        loadingOverlay.style.transition = "0.5s";
        loadingOverlay.style.opacity = "1";
      }, 0);
    }

  </script>
