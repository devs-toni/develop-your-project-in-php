<?php
require_once('Connection.php');

class Repository extends Connection
{

  // USERS

  function addUser(User $user): void
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'INSERT INTO users (rol, username, email, password) VALUES (?,?,?,?)');

    $userRol = $user->getRol();
    $userName = $user->getUserName();
    $userEmail = $user->getEmail();
    $userPass = $user->getPassword();

    $pre->bind_param('ssss', $userRol, $userName, $userEmail, $userPass);
    $pre->execute();
    $pre->close();
    $this->con->close();
  }

  function isAdmin(int $id)
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'SELECT rol FROM users WHERE id=?');
    $pre->bind_param('i', $id);
    $pre->execute();
    $result = $pre->get_result();

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        if ($row['rol'] === 'A') {
          $pre->close();
          $this->con->close();
          return true;
        }
      }
    }
    $pre->close();
    $this->con->close();
    return false;
  }

  function getUserById($id)
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'SELECT email, username, rol FROM users WHERE id=?');
    $pre->bind_param('i', $id);
    $pre->execute();
    $result = $pre->get_result();

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        $res['email'] = $row['email'];
        $res['rol'] = $row['rol'];
        $res['username'] = $row['username'];
      }
    } else {
      $res = [];
    }
    $pre->close();
    $this->con->close();
    return $res;
  }

    function getUserByEmail($mail)
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'SELECT id, password FROM users WHERE email=?');
    $pre->bind_param('s', $mail);
    $pre->execute();
    $result = $pre->get_result();

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        $res['pass'] = $row['password'];
        $res['id'] = $row['id'];
      }
    } else {
      $res = [];
    }
    $pre->close();
    $this->con->close();
    return $res;
  }
  
  function updateUserField($username, $email, $password, $id)
  {

    if ($username) {
      $field = $username;
      $val = 'username';
    }
    if ($email) {
      $field = $email;
      $val = 'email';
    }
    if ($password) {
      $field = password_hash($password, PASSWORD_DEFAULT);
      $val = 'password';
    }
    $this->connect();
    $pre = mysqli_prepare($this->con, "UPDATE users SET $val=? WHERE id=?");
    $pre->bind_param('si', $field, $id);
    $pre->execute();
    $result = $pre->get_result();
    $pre->close();
    $this->con->close();
    return $result;
  }

  // FILMS

  function addFilm(Movie $film): void
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'INSERT INTO movies (id, title, language, description, poster_path, release_date, vote_average, vote_count) VALUES (?,?,?,?,?,?,?,?)');

    $id = $film->getId();
    $title = $film->getTitle();
    $lang = $film->getLanguage();
    $desc = $film->getDescription();
    $poster = $film->getPosterPath();
    $date = $film->getReleaseDate();
    $vote_average = $film->getVoteAverage();
    $vote_count = $film->getVoteCount();

    $pre->bind_param('isssssdi', $id, $title, $lang, $desc, $poster, $date, $vote_average, $vote_count);
    $pre->execute();
    $pre->close();
    $this->con->close();
  }

  function existFilm($id)
  {
    $this->connect();
    $pre = mysqli_prepare($this->con, 'SELECT title FROM movies WHERE id = ?');
    $pre->bind_param('i', $id);
    $pre->execute();
    $result = $pre->get_result();

    if (mysqli_num_rows($result) > 0) {
      $response = true;
    } else {
      $response = false;
    }
    $pre->close();
    $this->con->close();
    return $response;
  }

  function getAllFilms()
  {
    $this->connect();
    $allPosterMovies = [];
    $result = mysqli_query($this->con, 'SELECT title, poster_path FROM movies');
    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        $allPosterMovies[$row['title']] = $row["poster_path"];
      }
    } else {
      echo "0 results";
    }
    $this->con->close();
    return $allPosterMovies;
  }

  function getMostVotedMovies()
  {
    $ids = [];
    $titles = [];
    $posters = [];
    $posterMovies = [];

    $this->connect();
    $allPosterMovies = [];
    $result = mysqli_query($this->con, 'SELECT id, title, poster_path FROM movies ORDER BY vote_count DESC LIMIT 20');
    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        array_push($ids, $row['id']);
        array_push($titles, $row['title']);
        array_push($posters, $row['poster_path']);
      }
    } else {
      echo "0 results";
    }
    array_push($posterMovies, $ids, $titles, $posters);
    $this->con->close();
    return $posterMovies;
  }

  function getPaginationMovies($min, $size)
  {
    $this->connect();

    $ids = [];
    $titles = [];
    $posters = [];
    $posterMovies = [];

    $pre = mysqli_prepare($this->con, 'SELECT id, title, poster_path FROM movies LIMIT ?, ?');
    $pre->bind_param('ii', $min, $size);
    $pre->execute();
    $result = $pre->get_result();

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        array_push($ids, $row['id']);
        array_push($titles, $row['title']);
        array_push($posters, $row['poster_path']);
      }
    } else {
      echo "0 results";
    }
    array_push($posterMovies, $ids, $titles, $posters);
    $this->con->close();
    return $posterMovies;
  }

  function deleteFilms()
  {
    $this->connect();
    mysqli_query($this->con, 'DELETE FROM MOVIES');
    $this->con->close();
  }

  function getSearchMovies($movie)
  {
    $this->connect();

    $ids = [];
    $titles = [];
    $posters = [];
    $searchMovies = [];

    $result = mysqli_query($this->con, 'SELECT id, title, poster_path FROM movies WHERE title COLLATE utf8mb4_general_ci LIKE "%' . $movie . '%"');
    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        array_push($ids, $row['id']);
        array_push($titles, $row['title']);
        array_push($posters, $row['poster_path']);
      }
    } else {
      echo "0 results";
    }
    array_push($searchMovies, $ids, $titles, $posters);
    $this->con->close();
    return $searchMovies;
  }

  function getInfoFilm(string $filmId)
  {
    $queryInfoFilm = 'SELECT id, title, description, poster_path, release_date, vote_count FROM movies 
        WHERE id=?';
    $queryComments = 'SELECT text, username FROM comments 
        INNER JOIN users ON comments.id_user = users.id
        WHERE comments.id_movie = ?';

    $this->connect();
    $pre = mysqli_prepare($this->con, $queryInfoFilm);
    $pre->bind_param("s", $filmId);
    $pre->execute();
    $res = $pre->get_result();

    while ($row = $res->fetch_assoc()) {
      $idMovie = $row['id'];
      $info = (object) [
        "title" => $row["title"],
        "description" => $row["description"],
        "imgPath" => $row["poster_path"],
        "date" => $row["release_date"],
        "rate" => $row["vote_count"],
        "comments" => []
      ];
    }
    $pre->close();

    $pre = mysqli_prepare($this->con, $queryComments);
    $pre->bind_param("s", $idMovie);
    $pre->execute();
    $res2 = $pre->get_result();

    $comments = [];
    if (mysqli_num_rows($res2) > 0) {
      while ($row = $res2->fetch_assoc()) {
        $comment = (object) [
          "username" => $row["username"],
          "comment" => $row["text"]
        ];
        array_push($comments, $comment);
      }
    }

    $info->comments = $comments;

    $pre->close();
    $this->con->close();
    return $info;
  }

  //LIKES

  function checkIfisAlreadyRated(string $filmId, string $userId)
  {

    $querySelect = "SELECT id FROM likes WHERE id_movie=? && id_user=?";

    $this->connect();
    $pre = mysqli_prepare($this->con, $querySelect);
    $pre->bind_param("ss", $filmId, $userId);
    $pre->execute();
    $res = $pre->get_result();

    if (mysqli_num_rows($res) > 0) {
      while ($row = $res->fetch_assoc()) {
        $idLike = $row['id'];
      }
    } else {
      $idLike = "";
    }

    $pre->close();
    $this->con->close();
    return $idLike;
  }

  function deleteLike(string $likeId)
  {
    $queryDelete = "DELETE FROM likes WHERE id=?";
    $this->connect();
    $pre = mysqli_prepare($this->con, $queryDelete);
    $pre->bind_param("s", $likeId);
    $pre->execute();

    $pre->close();
    $this->con->close();
  }

  function insertLike(string $filmId, string $userId)
  {
    $queryInsert = "INSERT INTO likes (id_user, id_movie) VALUES (?, ?)";

    $this->connect();
    $pre = mysqli_prepare($this->con, $queryInsert);
    $pre->bind_param("ss", $userId, $filmId);
    $pre->execute();

    $pre->close();
    $this->con->close();
  }

  function addLikeRate(string $filmId)
  {
    $queryUpdateRateAdd = "UPDATE movies SET vote_count = (@cur_value := vote_count) + 1 WHERE id=?";

    $this->connect();
    $pre = mysqli_prepare($this->con, $queryUpdateRateAdd);
    $pre->bind_param("s", $filmId);
    $pre->execute();

    $pre->close();
    $this->con->close();
  }

  function substrLikeRate(string $filmId)
  {
    $queryUpdateRateSub = "UPDATE movies SET vote_count = (@cur_value := vote_count) - 1 WHERE id=?";

    $this->connect();
    $pre = mysqli_prepare($this->con, $queryUpdateRateSub);
    $pre->bind_param("s", $filmId);
    $pre->execute();

    $pre->close();
    $this->con->close();
  }

  //COMMENTS

  function addCommentFilm(string $userId, string $filmId, string $comment)
  {
    $query = "INSERT INTO comments (id_user, id_movie, text) VALUES (?,?,?)";
    $queryComments = 'SELECT text, username FROM comments 
        INNER JOIN users ON comments.id_user = users.id
        WHERE comments.id_movie = ? && comments.id_user = ? && comments.text = ?';

    $this->connect();
    $pre = mysqli_prepare($this->con, $query);
    $pre->bind_param("sss", $userId, $filmId, $comment);
    $pre->execute();
    $pre->close();

    $pre = mysqli_prepare($this->con, $queryComments);
    $pre->bind_param("sss", $filmId, $userId, $comment);
    $pre->execute();
    $res = $pre->get_result();

    if (mysqli_num_rows($res) > 0) {
      while ($row = $res->fetch_assoc()) {
        $comment = (object) [
          "username" => $row["username"],
          "comment" => $row["text"]
        ];
      }
    }

    $pre->close();
    $this->con->close();
    return $comment;
  }
}