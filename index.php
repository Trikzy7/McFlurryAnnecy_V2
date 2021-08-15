

<!DOCTYPE html>
<html>
  <head>
    <title>Annecy McFlurry</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAeTlM2-93T2TwzAVf-tpTAoCxlfHGSnqc&libraries=&v=weekly"></script>
    <script src="app.js" type="module"></script>
    <style type="text/css">
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }

      /* Optional: Makes the sample page fill the window. */
      html,
      body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>



  </head>


  <body>
    <div id="map"></div>

    <?php
      include('mcdo.php');
    ?>

  </body>
</html>