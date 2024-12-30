function runSlider(device) {
  /* Modals */
  const slides = document.querySelectorAll('.slide');
  const modalOverlay = document.querySelector('.modal-overlay');
  const modalContent = document.querySelector('.modal-content');
  const modalTitle = modalContent.querySelector('.modal-title');
  const modalDescription = modalContent.querySelector('.modal-description');
  const modalClose = modalContent.querySelector('.modal-close');

  // Function to handle the slide click
  function handleSlideClick(event) {
    const slide = event.currentTarget;
    const title = slide.querySelector('.slide-title')?.textContent || 'No Title';
    const description = slide.querySelector('.slide-description')?.textContent || 'No Description';

    // Populate modal with slide details
    modalTitle.textContent = title;
    modalDescription.textContent = description;

    // Show the modal
    modalOverlay.style.display = 'flex';
  }

  // Add click event listener to each slide
  slides.forEach((slide) => {
    slide.addEventListener('click', handleSlideClick);
  });

  // Close the modal when clicking on the close button or outside the modal content
  modalClose.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
  });

  modalOverlay.addEventListener('click', (event) => {
    if (event.target === modalOverlay) {
      modalOverlay.style.display = 'none';
    }
  });

  const sliderWrapper = document.querySelector('.slider-wrapper');
  const sliderTrack = document.querySelector('.slider-track');
  const prevBtn = document.querySelector('.nav-arrow.prev');
  const nextBtn = document.querySelector('.nav-arrow.next');
  const paginationEl = document.querySelector('.pagination');

  const slideWidth = device === 'desk' ? 300 : 240;
  const slideGap = device === 'desk' ? 37.33 : 16;
  const slidesPerView = device === 'desk' ? 4 : 1;
  const realSlides = Array.from(slides); // Original slides
  const totalSlides = realSlides.length;
  let currentSlideIndex = 0;
  let isTransitioning = false;

  // Clone slides for infinite scroll
  function cloneSlides() {
    if (totalSlides >= slidesPerView) {
      for (let i = 0; i < slidesPerView; i++) {
        sliderTrack.prepend(realSlides[totalSlides - 1 - i].cloneNode(true));
        sliderTrack.append(realSlides[i].cloneNode(true));
      }
    }
  }

  // Center the slide
  function centerSlide(index, instant = false) {
    const offset = device === 'desk'
      ? (slideWidth + slideGap) * Math.floor(index / slidesPerView) * slidesPerView
      : (slideWidth + slideGap) * index - (sliderWrapper.offsetWidth - slideWidth) / 2;

    sliderTrack.style.transition = instant ? 'none' : 'transform 0.5s ease';
    sliderTrack.style.transform = `translateX(${-offset}px)`;
    currentSlideIndex = index;

    updatePagination();
  }

  // Adjust for infinite scroll
  function adjustForInfiniteScroll() {
    if (totalSlides >= slidesPerView) {
      const realSlideCount = totalSlides;
      const totalClones = slidesPerView;

      if (currentSlideIndex < totalClones) {
        currentSlideIndex += realSlideCount;
        centerSlide(currentSlideIndex, true);
      } else if (currentSlideIndex >= realSlideCount + totalClones) {
        currentSlideIndex -= realSlideCount;
        centerSlide(currentSlideIndex, true);
      }
    }
    isTransitioning = false;
  }

  // Build pagination
  function buildPagination() {
    paginationEl.innerHTML = '';
    const totalDots = Math.ceil(totalSlides / (device === 'desk' ? slidesPerView : 1));
    for (let i = 0; i < totalDots; i++) {
      const dot = document.createElement('div');
      dot.classList.add('dot');
      dot.addEventListener('click', () => {
        if (!isTransitioning) {
          const targetIndex = device === 'desk' ? i * slidesPerView : i;
          centerSlide(targetIndex);
        }
      });
      paginationEl.appendChild(dot);
    }
    updatePagination();
  }

  // Update pagination
  function updatePagination() {
    const dots = paginationEl.querySelectorAll('.dot');
    const activeIndex = device === 'desk'
      ? Math.floor(currentSlideIndex / slidesPerView) % Math.ceil(totalSlides / slidesPerView)
      : currentSlideIndex % totalSlides;
    dots.forEach((dot, i) => dot.classList.toggle('active', i === activeIndex));
  }

  // Navigation
  function goToNext() {
    if (isTransitioning) return;
    isTransitioning = true;

    currentSlideIndex += device === 'desk' ? slidesPerView : 1;
    centerSlide(currentSlideIndex);
    if (totalSlides >= slidesPerView) setTimeout(adjustForInfiniteScroll, 600);
    else isTransitioning = false;
  }

  function goToPrev() {
    if (isTransitioning) return;
    isTransitioning = true;

    currentSlideIndex -= device === 'desk' ? slidesPerView : 1;
    centerSlide(currentSlideIndex);
    if (totalSlides >= slidesPerView) setTimeout(adjustForInfiniteScroll, 600);
    else isTransitioning = false;
  }

  // Initialize slider
  function initSlider() {
    if (totalSlides < slidesPerView) {
      sliderTrack.style.transition = 'none';
      centerSlide(0, true); // Center without cloning
    } else {
      cloneSlides();
      currentSlideIndex = device === 'desk' ? slidesPerView : 1;
      centerSlide(currentSlideIndex, true);
    }

    buildPagination();

    if (totalSlides < slidesPerView || device !== "desk") {
      prevBtn.style.display = 'none';
      nextBtn.style.display = 'none';
  } else {
      prevBtn.style.display = 'block';
      nextBtn.style.display = 'block';
  }
  }

  prevBtn.addEventListener('click', goToPrev);
  nextBtn.addEventListener('click', goToNext);
  window.addEventListener('resize', initSlider);

  initSlider();
}

let device = window.innerWidth < 768 ? 'mob' : 'desk';
runSlider(device);
