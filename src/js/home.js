'use strict';

    class Carousel {
      constructor(el) {
        this.el = el;
        this.carouselData = [
          {
            'id': '1',
            'src': '../content-images/img1.jpg'
          },
          {
            'id': '2',
            'src': '../content-images/img2.jpg'
          },
          {
            'id': '3',
            'src': '../content-images/img3.jpg'
          },
          {
            'id': '4',
            'src': '../content-images/img4.jpg'
          },
          {
            'id': '5',
            'src': '../content-images/img5.jpg'
          }
        ];
        this.carouselInView = [1, 2, 3, 4, 5];
        this.carouselContainer;
        this.carouselPlayState;
      }

      mounted() {
        this.setupCarousel();
        this.play(); // Start autoplay when the carousel is mounted
      }

      // Build carousel html
      setupCarousel() {
        const container = document.createElement('div');

        // Add container for carousel items
        this.el.append(container);
        container.className = 'carousel-container';

        // Take dataset array and append items to container
        this.carouselData.forEach((item, index) => {
          const carouselItem = item.src ? document.createElement('img') : document.createElement('div');

          container.append(carouselItem);
          
          // Add item attributes
          carouselItem.className = `carousel-item carousel-item-${index + 1}`;
          carouselItem.src = item.src;
          carouselItem.setAttribute('loading', 'lazy');
          // Used to keep track of carousel items, infinite items possible in carousel however min 5 items required
          carouselItem.setAttribute('data-index', `${index + 1}`);
        });

        // Set container property
        this.carouselContainer = container;
      }

      play() {
        const startPlaying = () => this.next();

        // Use play state prop to store interval ID and run next method on a 1.5 second interval
        this.carouselPlayState = setInterval(startPlaying, 1500);
      }

      next() {
        // Update order of items in data array to be shown in carousel
        this.carouselData.push(this.carouselData.shift());

        // Take the last item and add it to the beginning of the array so that the next item is front and center
        this.carouselInView.unshift(this.carouselInView.pop());

        // Update the css class for each carousel item in view
        this.carouselInView.forEach((item, index) => {
          this.carouselContainer.children[index].className = `carousel-item carousel-item-${item}`;
        });

        // Using the first 5 items in data array update content of carousel items in view
        this.carouselData.slice(0, 5).forEach((data, index) => {
          document.querySelector(`.carousel-item-${index + 1}`).src = data.src;
        });
      }
    }

    const el = document.querySelector('.carousel');
    // Create a new carousel object
    const exampleCarousel = new Carousel(el);
    // Setup carousel and methods
    exampleCarousel.mounted();

let li = document.querySelectorAll(".faq-text li");
    for (var i = 0; i < li.length; i++) {
      li[i].addEventListener("click", (e)=>{
        let clickedLi;
        if(e.target.classList.contains("question-arrow")){
          clickedLi = e.target.parentElement;
        }else{
          clickedLi = e.target.parentElement.parentElement;
        }
       clickedLi.classList.toggle("showAnswer");
      });
    }