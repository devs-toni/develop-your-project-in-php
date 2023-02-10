const searchInput = document.getElementById("searchInput");
const tableBody = document.getElementById('tableBody');
let adminPage = false;
const title = document.querySelector(".all-films__title");

searchInput && searchInput.addEventListener("keyup", searchMovies);

function searchMovies() {
  let movie = searchInput.value;
  const paginationContainer = document.getElementById("paginationContainer");
  const listTrend = document.querySelector(".top-trend");
  const listSpa = document.querySelector(".top-spanish");
  const listP = document.querySelector(".top-p");
  const listVotes = document.querySelector(".top-votes");
  listTrend && listTrend.classList.add('hidden');
  listVotes && listVotes.classList.add('hidden');
  listSpa && listSpa.classList.add('hidden');
  listP && listP.classList.add('hidden');
  if (movie.length >= 2) {
    title ? title.textContent = "Search Results" : "";
    fetch("src/controllers/Search.php?schMovies=" + movie, {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (!adminPage) {
          paginationContainer.style.visibility = "hidden";
          printFilms(data, '#paginatedList');
        } else {
          searchEditFilms(data);
        }
      });
  }

  if (movie.length == 0) {
    title ? title.textContent = "Catalogue" : "";
    if (!adminPage) {
      listTrend && listTrend.classList.remove('hidden');
      listVotes && listVotes.classList.remove('hidden');
      listSpa && listSpa.classList.remove('hidden');
      listP && listP.classList.remove('hidden');
      setCurrentPage(currentPage);
      paginationContainer.style.visibility = "visible";
    } else {
      tableBody.innerHTML = "";
    }
  }
}
