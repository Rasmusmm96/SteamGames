<?php
  if (!isset($_COOKIE["SteamID"])) {
    header('Location: ./login.php');
    exit;
  }

  $steamid = $_COOKIE["SteamID"];

  $url = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=54B10DA7F435608803C98AF5C0227026&steamid='. $steamid .'&include_appinfo=1&include_played_free_games=1&format=json';
  $obj = json_decode(file_get_contents($url), true);
  $games = $obj['response']['games'];
?>
<html>
  <head>
    <title>Steamgames</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </head>
  <style>
    body {
      background-image: url("http://cdn.akamai.steamstatic.com/steam/apps/220/page_bg_generated_v6b.jpg?t=1515390717");
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
    .gamecard {
      min-width: 355px;
      max-width: 460px;
      margin: 15px auto;
      display: flex;
    }
  </style>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <a class="navbar-brand" href="./">Steamgames</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Sortér
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href=".?sort=nameasc">Navn - A-Å</a>
              <a class="dropdown-item" href=".?sort=namedesc">Navn - Å-A</a>
              <a class="dropdown-item" href=".?sort=timeasc">Spilletid - Lav-Høj</a>
              <a class="dropdown-item" href=".?sort=timedesc">Spilletid - Høj-Lav</a>
            </div>
          </li>
        </ul>
        <form class="form-inline my-2 my-lg-0" action="./" method="get">
          <input class="form-control mr-sm-2" name="query" type="text" placeholder="Spiltitel" aria-label="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Søg</button>
        </form>
        <button onclick="logout()" class="btn btn-outline-danger my-2 my-sm-0" type="submit" style="margin-left: 10px;">Logud</button>
      </div>
    </nav>
    <?php

    $targets = array('Beta', 'Test', 'SDK');

    if (isset($_GET['query'])) {
      $query = strtolower($_GET['query']);
      $newGames = [];

      foreach ($games as $game) {
        $gamename = strtolower($game['name']);
        if (strpos($gamename, $query) !== false) {
          $newGames[] = $game;
        }
      }

      $games = $newGames;
    }

    if (isset($_GET['sort'])) {
      switch ($_GET['sort']) {
        case 'timedesc':
          usort($games, function($a, $b) {
            return $a['playtime_forever'] <=> $b['playtime_forever'];
          });
          $games = array_reverse($games);
          break;
        case 'timeasc':
          usort($games, function($a, $b) {
            return $a['playtime_forever'] <=> $b['playtime_forever'];
          });
          break;
        case 'nameasc':
          usort($games, function($a, $b) {
            return $a['name'] <=> $b['name'];
          });
          break;
        case 'namedesc':
          usort($games, function($a, $b) {
            return $a['name'] <=> $b['name'];
          });
          $games = array_reverse($games);
          break;
        default:
          break;
      }
    }

    echo '<div class="container-fluid"><div class="row">';

    foreach ($games as $game) {
      $show = true;
      $imgsrc = 'http://cdn.akamai.steamstatic.com/steam/apps/' . $game['appid'] . '/header.jpg';
      $altimgsrc = 'http://media.steampowered.com/steamcommunity/public/images/apps/'. $game['appid'] .'/'. $game['img_logo_url'] .'.jpg';

      foreach($targets as $t)
      {
          if (strpos($game['name'],$t) !== false) {
              $show = false;
              break;
          }
      }

      if ($game['img_icon_url'] == '' || $game['img_logo_url'] == '') {
        $show = false;
      }

      if ($show) {
        echo '
          <div class="col">
            <div class="card gamecard">
              <a href="./game.php?appid='.$game['appid'].'"><img class="card-img-top" src="' . $imgsrc . '" alt="Card image cap"></a>
              <div class="card-body">
                <h5 class="card-title" style="min-height: 48px;">
                  ' . $game['name'] . '
                </h5>
                <p class="card-text">
                Spilletid: ' . round($game['playtime_forever'] / 60,2) . ' Timer
                </p>
                <a href="steam://run/'. $game['appid'] .'" class="btn btn-primary">Start spil</a>
              </div>
            </div>
          </div>
        ';
      }
    }

    echo '</div></div>'
    ?>
  </body>
  <script>
  function logout() {
    document.cookie = "SteamID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/steamgames;";
    window.location.href = "./login.php";
  }
 </script>
</html>
