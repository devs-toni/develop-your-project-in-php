const url = "https://image.tmdb.org/t/p/w500";
let currentPage;
const paginationLimit = 24;

function doPagination(total) {
  window.addEventListener("load", () => {
    loadFilms(total);
  });
}

function loadFilms(total) {
  const nextButton = document.getElementById("nextButton");
  const prevButton = document.getElementById("prevButton");
  let pageCount = Math.ceil(total / paginationLimit);

  getPaginationNumbers(pageCount);
  setCurrentPage(1);
  prevButton.addEventListener("click", () => {
    setCurrentPage(currentPage - 1);
  });
  nextButton.addEventListener("click", () => {
    setCurrentPage(currentPage + 1);
  });
  document.querySelectorAll(".pg-num").forEach((button) => {
    const pageIndex = Number(button.getAttribute("page-index"));
    if (pageIndex) {
      button.addEventListener("click", () => {
        setCurrentPage(pageIndex);
      });
    }
  });
}

const getPaginationNumbers = (pageCount) => {
  for (let i = 1; i <= pageCount; i++) {
    appendPageNumber(i);
  }
};

const handleActivePageNumber = () => {
  document.querySelectorAll(".pg-num").forEach((button) => {
    button.classList.remove("active");

    const pageIndex = Number(button.getAttribute("page-index"));
    if (pageIndex == currentPage) {
      button.classList.add("active");
    }
  });
};

const disableButton = (button) => {
  button.classList.add("disabled");
  button.setAttribute("disabled", true);
};

const enableButton = (button) => {
  button.classList.remove("disabled");
  button.removeAttribute("disabled");
};

const setCurrentPage = async (pageNum, pageCount) => {
  currentPage = pageNum;
  handleActivePageNumber();
  handlePageButtonsStatus(pageCount);

  const prevRange = (pageNum - 1) * paginationLimit;

  await fetch(`src/controllers/PaginationResults.php?min=${prevRange}`)
    .then((res) => res.json())
    .then((res) => {
      printFilms(res, '#paginatedList');
    })
    .catch((err) => {
      console.error(err);
    });
};

const handlePageButtonsStatus = (pageCount) => {
  if (currentPage === 1) {
    disableButton(prevButton);
  } else {
    enableButton(prevButton);
  }
  if (pageCount === currentPage) {
    disableButton(nextButton);
  } else {
    enableButton(nextButton);
  }
};

const appendPageNumber = (index) => {
  const paginationNumbers = document.getElementById("paginationNumbers");
  const pageNumber = document.createElement("button");
  pageNumber.className = "pg-num";
  pageNumber.innerHTML = index;
  pageNumber.setAttribute("page-index", index);
  pageNumber.setAttribute("aria-label", "Page " + index);
  paginationNumbers.appendChild(pageNumber);
};

async function printFilms(res, container) {
  let results = res;
  let printContainer = document.querySelector(container);
  if (results) printContainer.innerHTML = "";
  if (!results) {
    await fetch(`src/controllers/Top10Results.php`)
      .then((res) => res.json())
      .then((r) => {
        results = r;
      })
      .catch((err) => {
        console.error(err);
      });
  }

  for (let i = 0; i < results[0].length; i++) {
    res ?
      printContainer.innerHTML += `<li><img class="lazy" src="${results[2][i]}" alt="${results[1][i]}" data-id="${results[0][i]}"><div class="skeleton"></div></li>`
      :
      printContainer.innerHTML += `<div class="carousel__film"><img class="lazy" src="${results[2][i]}" alt="${results[1][i]}" data-id="${results[0][i]}"><div class="skeleton"></div></div>`;

  }
  printContainer.classList.remove("hidden");
  let lazyLoadImages = document.querySelectorAll(`${container} li img`);
  lazyLoadImages.forEach((i) => {
    i.addEventListener("click", openInfoFilm);
    i.onload = () => {
      testImage(i);
    };
  });
}

function testImage(image) {
  const tester = new Image();
  tester.onload = imageFound(image);
}

function imageFound(image) {
  image.parentNode.lastChild.classList.add("hidden");
  image.classList.remove("lazy");
}
function openInfoFilm(e) {
  window.location.href = "infoFilm.php?film=" + e.target.dataset.id;
}

