<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en" ng-app="phonecatApp">
<head>
  <meta charset="utf-8">
  <title>My HTML File</title>
  <link rel="stylesheet" href="public/components/bootstrap/dist/css/bootstrap.min.css">
  <!--link rel="stylesheet" href="css/app.css"-->

  <script src="public/angular/angular.min.js"></script>
  <script src="public/jquery/dist/jquery.min.js"></script>
  <script src="public/js/project.js"></script>

</head>
<body>

<ul>
    <li ng-repeat="phone in phones">
        <span>{{phone.name}}</span>
        <p>{{phone.snippet}}</p>
    </li>
</ul>


</body>
</html>