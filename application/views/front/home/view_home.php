<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Oswald:wght@500&display=swap");

$primary-color: #ecad29;
$text-color: #FFFFFFDD;

body {
    margin: 0;
    background-color: #1a1a1a;
    color: $text-color;
    position: relative;
    overflow: hidden;
    font-family: "Inter", sans-serif;
}

.card {
    position: absolute;
    left: 0;
    top: 0;
    background-position: center;
    background-size: cover;
    box-shadow: 6px 6px 10px 2px rgba(0, 0, 0, 0.6);
}

#btn {
    position: absolute;
    top: 690px;
    left: 16px;
    z-index: 99;
}

.card-content {
    position: absolute;
    left: 0;
    top: 0;
    color: $text-color;

    padding-left: 16px;
}

.content-place {
    margin-top: 6px;
    font-size: 13px;
    font-weight: 500;
}

.content-place {
    font-weight: 500;
}

.content-title-1,
.content-title-2 {
    font-weight: 600;
    font-size: 20px;
    font-family: "Oswald", sans-serif;
}

.content-start {
    width: 30px;
    height: 5px;
    border-radius: 99px;
    background-color: $text-color;
}

.details {
    z-index: 22;
    position: absolute;
    top: 240px;
    left: 60px;

    .place-box {
        height: 46px;

        overflow: hidden;

        .text {
            padding-top: 16px;
            font-size: 20px;

            &:before {
                top: 0;
                left: 0;
                position: absolute;
                content: "";
                width: 30px;
                height: 4px;
                border-radius: 99px;
                background-color: white;
            }
        }
    }

    .title-1,
    .title-2 {
        font-weight: 600;
        font-size: 72px;
        font-family: "Oswald", sans-serif;
    }

    .title-box-1,
    .title-box-2 {
        margin-top: 2px;
        height: 100px;
        overflow: hidden;
        // background-color: blue;
    }

    >.desc {
        margin-top: 16px;
        width: 500px;
    }

    >.cta {
        width: 500px;
        margin-top: 24px;
        display: flex;
        align-items: center;

        >.bookmark {
            border: none;
            background-color: $primary-color;
            width: 36px;
            height: 36px;
            border-radius: 99px;
            color: white;
            display: grid;
            place-items: center;

            svg {
                width: 20px;
                height: 20px;
            }
        }

        >.discover {
            border: 1px solid #ffffff;
            background-color: transparent;
            height: 36px;
            border-radius: 99px;
            color: #ffffff;
            padding: 4px 24px;
            font-size: 12px;
            margin-left: 16px;
            text-transform: uppercase;
        }
    }
}

nav {
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 36px;
    font-weight: 500;

    svg {
        width: 20px;
        height: 20px;
    }

    .svg-container {
        width: 20px;
        height: 20px;
    }

    >div {
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        font-size: 14px;

        &:first-child {
            gap: 10px;
        }

        &:last-child {
            gap: 24px;

            >.active {
                position: relative;

                &:after {
                    bottom: -8px;
                    left: 0;
                    right: 0;
                    position: absolute;
                    content: "";
                    height: 3px;
                    border-radius: 99px;
                    background-color: $primary-color;
                }
            }
        }
    }
}

.indicator {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    height: 5px;
    z-index: 60;
    background-color: $primary-color;
}

.pagination {
    position: absolute;
    left: 0px;
    top: 0px;
    display: inline-flex;

    >.arrow {
        z-index: 60;
        width: 50px;
        height: 50px;
        border-radius: 999px;
        border: 2px solid #ffffff55;
        display: grid;
        place-items: center;

        &:nth-child(2) {
            margin-left: 20px;
        }

        svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            color: #ffffff99;
        }
    }

    .progress-sub-container {
        margin-left: 24px;
        z-index: 60;
        width: 500px;
        height: 50px;
        display: flex;
        align-items: center;

        .progress-sub-background {
            width: 500px;
            height: 3px;
            background-color: #ffffff33;

            .progress-sub-foreground {
                height: 3px;
                background-color: $primary-color;
            }
        }
    }

    .slide-numbers {
        width: 50px;
        height: 50px;
        overflow: hidden;
        // background-color: #111111;
        z-index: 60;
        position: relative;

        .item {
            width: 50px;
            height: 50px;
            position: absolute;
            // background: rgb(31, 31, 41);
            color: white;
            top: 0;
            left: 0;
            display: grid;
            place-items: center;
            font-size: 32px;
            font-weight: bold;
        }
    }
}


.cover {
    position: absolute;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background-color: #fff;
    z-index: 100;
}
</style>

<main class="home-main">
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Header Start -->
    <div class="container-fluid p-0" style="">
        <section class="creative-fullpage--slider">
            <div class="indicator"></div>
            <div id="demo"></div>
            <div class="details" id="details-even">
                <div class="place-box">
                    <div class="text">Switzerland Alps</div>
                </div>
                <div class="title-box-1">
                    <div class="title-1">SAINT</div>
                </div>
                <div class="title-box-2">
                    <div class="title-2">ANTONIEN</div>
                </div>
                <div class="desc">
                    Tucked away in the Switzerland Alps, Saint Antönien offers an idyllic retreat for those seeking
                    tranquility and adventure alike. It's a hidden gem for backcountry skiing in winter and boasts lush
                    trails for hiking and mountain biking during the warmer months.
                </div>
                <div class="cta">
                    <button class="bookmark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button class="discover">Discover Location</button>
                </div>
            </div>

            <div class="details" id="details-odd">
                <div class="place-box">
                    <div class="text">Switzerland Alps</div>
                </div>
                <div class="title-box-1">
                    <div class="title-1">SAINT </div>
                </div>
                <div class="title-box-2">
                    <div class="title-2">ANTONIEN</div>
                </div>
                <div class="desc">
                    Tucked away in the Switzerland Alps, Saint Antönien offers an idyllic retreat for those seeking
                    tranquility and adventure alike. It's a hidden gem for backcountry skiing in winter and boasts lush
                    trails for hiking and mountain biking during the warmer months.
                </div>
                <div class="cta">
                    <button class="bookmark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button class="discover">Discover Location</button>
                </div>
            </div>

            <div class="pagination" id="pagination">
                <div class="arrow arrow-left">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </div>
                <div class="arrow arrow-right">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
                <div class="progress-sub-container">
                    <div class="progress-sub-background">
                        <div class="progress-sub-foreground"></div>
                    </div>
                </div>
                <div class="slide-numbers" id="slide-numbers"></div>
            </div>

            <div class="cover"></div>
        </section>

    </div>
    <!-- Header End -->
    <!-- About Start -->
    <div class="container-fluid bg-secondary">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-7 pb-0 pb-lg-5 py-5">
                    <div class="pb-0 pb-lg-5 py-5">
                        <div class="title wow fadeInUp" data-wow-delay="0.1s">
                            <div class="title-left">
                                <h5>Who We Are</h5>
                                <h1>About HyrEvo</h1>

                            </div>
                        </div>
                        <p class="mb-4 wow fadeInUp" data-wow-delay="0.2s">HyrEvo is a web-based recruitment management
                            system designed to connect
                            agencies, recruiters, and candidates on one secure platform. Our goal is
                            to eliminate scattered communication, manual screening, and slow hiring
                            cycles by offering smart tools, structured workflows, and real-time
                            collaboration features that help companies find the right talent fast.

                        </p>
                        <ul class="list-group list-group-flush mb-5 wow fadeInUp" data-wow-delay="0.3s">
                            <li class="list-group-item bg-dark text-body border-secondary ps-0">
                                <i class="fa fa-check-circle text-primary me-1"></i> Manage recruiters, agencies, and
                                candidates in one ecosystem.
                            </li>
                            <li class="list-group-item bg-dark text-body border-secondary ps-0">
                                <i class="fa fa-check-circle text-primary me-1"></i> Track job submissions, ratings, and
                                onboarding progress.
                            </li>
                            <li class="list-group-item bg-dark text-body border-secondary ps-0">
                                <i class="fa fa-check-circle text-primary me-1"></i> Communicate instantly with a
                                built-in chat system.
                            </li>

                        </ul>
                        <div class="row wow fadeInUp" data-wow-delay="0.4s">
                            <div class="col-6">
                                <a href="<?= site_url() ?>contact"
                                    class="btn btn-outline-primary border-2 py-3 w-100">Register As An
                                    Agency</a>
                            </div>
                            <div class="col-6">
                                <a href="<?= site_url() ?>contact" class="btn btn-primary py-3 w-100">Get in Touch</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 wow fadeInUp" data-wow-delay="0.5s">
                    <img class="img-fluid" src="resources/front/images/about2.png" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->
    <!-- Service Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center">
                <div class="title wow fadeInUp" data-wow-delay="0.1s">
                    <div class="title-center">
                        <h5>Services</h5>
                        <h1>How We Help You</h1>
                    </div>
                </div>
            </div>
            <div class="service-item service-item-left">
                <div class="row g-0 align-items-center">
                    <div class="col-md-5">
                        <div class="service-img p-5 wow fadeInRight" data-wow-delay="0.2s">
                            <img class="img-fluid rounded-circle" src="resources/front/images/service-2.png" alt="">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="service-text px-5 px-md-0 py-md-5 wow fadeInRight" data-wow-delay="0.5s">
                            <h3 class="text-uppercase">Hiring Progress</h3>
                            <p class="mb-4">
                                Track every step of the recruitment journey in real-time — from candidate submission,
                                screening, and rating, to onboarding and final job placement. HyrEvo ensures
                                transparency
                                and faster decision-making for all stakeholders.
                            </p>

                            <a class="btn btn-outline-primary border-2 px-4" href="<?= site_url() ?>contact">Learn More
                                <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="service-item service-item-right">
                <div class="row g-0 align-items-center">
                    <div class="col-md-5 order-md-1 text-md-end">
                        <div class="service-img p-5 wow fadeInLeft" data-wow-delay="0.2s">
                            <img class="img-fluid rounded-circle" src="resources/front/images/service-3.png" alt="">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="service-text px-5 px-md-0 py-md-5 text-md-end wow fadeInLeft" data-wow-delay="0.5s">
                            <h3 class="text-uppercase">Agency Portal</h3>
                            <p class="mb-4">
                                Agencies can create jobs, customize job forms, collaborate with recruiters, rate
                                candidates,
                                and manage onboarding. They also gain access to top-rated recommended candidates and
                                detailed
                                performance analytics.
                            </p>

                            <a class="btn btn-outline-primary border-2 px-4" href="<?= site_url() ?>contact">Learn More
                                <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="service-item service-item-left">
                <div class="row g-0 align-items-center">
                    <div class="col-md-5">
                        <div class="service-img p-5 wow fadeInRight" data-wow-delay="0.2s">
                            <img class="img-fluid rounded-circle" src="resources/front/images/service-4.png" alt="">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="service-text px-5 px-md-0 py-md-5 wow fadeInRight" data-wow-delay="0.5s">
                            <h3 class="text-uppercase">Recruiter's Portal</h3>
                            <p class="mb-4">
                                Recruiters can add candidates, assign them to open jobs, upload documentation,
                                communicate
                                with hiring teams, and track progress efficiently. Gain insights on performance and
                                improve
                                placements with analytics tools.
                            </p>

                            <a class="btn btn-outline-primary border-2 px-4" href="<?= site_url() ?>contact">Learn More
                                <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="service-item service-item-right">
                <div class="row g-0 align-items-center">
                    <div class="col-md-5 order-md-1 text-md-end">
                        <div class="service-img p-5 wow fadeInLeft" data-wow-delay="0.2s">
                            <img class="img-fluid rounded-circle" src="resources/front/images/service-7.png" alt="">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="service-text px-5 px-md-0 py-md-5 text-md-end wow fadeInLeft" data-wow-delay="0.5s">
                            <h3 class="text-uppercase">Job Listing</h3>
                            <p class="mb-4">
                                Candidates can search and apply for openings directly from the platform. Recruiters and
                                agencies
                                can manage applications, filter talent, and access structured candidate profiles with
                                required
                                documentation already uploaded.
                            </p>

                            <a class="btn btn-outline-primary border-2 px-4" href="<?= site_url() ?>contact">Learn More
                                <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->

    <!-- Banner Start -->
    <div class="container-fluid py-5 bg-secondary">
        <div class="container py-5">
            <div class="row g-0 justify-content-center">
                <div class="col-lg-7 text-center">
                    <div class="title mx-5 px-5 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="title-center">
                            <h5>Partnerships</h5>
                            <h1>Partner With HyrEvo</h1>
                        </div>
                    </div>
                    <p class="fs-5 mb-5 wow fadeInUp" data-wow-delay="0.2s">
                        Join our network and collaborate to build efficient hiring pipelines.
                        Whether you’re a recruitment agency, HR consultant, or enterprise, HyrEvo provides
                        the tools to scale your hiring process.
                    </p>
                    <div class="position-relative wow fadeInUp" data-wow-delay="0.3s">
                        <input class="form-control border-0 bg-dark rounded-pill w-100 py-4 ps-4 pe-5" type="text"
                            placeholder="Your business email">
                        <button type="button" class="btn btn-primary py-3 px-4 position-absolute top-0 end-0 me-2"
                            style="margin-top: 7px;">Request Partnership</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Banner End -->





</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
$(document).ready(function() {
    var swiper = new Swiper(".swiper-container-h", {
        direction: "horizontal",
        effect: "slide",
        autoplay: {
            delay: 10000,
            disableOnInteraction: false
        },
        parallax: true,
        speed: 1600,
        rtl: true,
        loop: true,
        loopFillGroupWithBlank: !0,

        mousewheel: {
            eventsTarged: ".swiper-slide",
            sensitivity: 1
        },
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        scrollbar: {
            el: ".swiper-scrollbar",
            hide: false,
            draggable: true
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            type: "progressbar"
        }
    });
    var swiper = new Swiper(".swiper-container-h1", {
        direction: "horizontal",
        effect: "slide",
        autoplay: false,
        parallax: true,
        speed: 1600,
        rtl: true,
        loop: true,
        loopFillGroupWithBlank: !0,
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        scrollbar: {
            el: ".swiper-scrollbar",
            hide: false,
            draggable: true
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            type: "bullets",
            clickable: "true"
        }
    });
});
</script>

<script>
const data = [{
        place: 'Switzerland Alps',
        title: 'SAINT',
        title2: 'ANTONIEN',
        description: 'Tucked away in the Switzerland Alps, Saint Antönien offers an idyllic retreat for those seeking tranquility and adventure alike. It\'s a hidden gem for backcountry skiing in winter and boasts lush trails for hiking and mountain biking during the warmer months.',
        image: 'https://assets.codepen.io/3685267/timed-cards-1.jpg'
    },
    {
        place: 'Japan Alps',
        title: 'NANGANO',
        title2: 'PREFECTURE',
        description: 'Nagano Prefecture, set within the majestic Japan Alps, is a cultural treasure trove with its historic shrines and temples, particularly the famous Zenkō-ji. The region is also a hotspot for skiing and snowboarding, offering some of the country\'s best powder.',
        image: 'https://assets.codepen.io/3685267/timed-cards-2.jpg'
    },
    {
        place: 'Sahara Desert - Morocco',
        title: 'MARRAKECH',
        title2: 'MEROUGA',
        description: 'The journey from the vibrant souks and palaces of Marrakech to the tranquil, starlit sands of Merzouga showcases the diverse splendor of Morocco. Camel treks and desert camps offer an unforgettable immersion into the nomadic way of life.',
        image: 'https://assets.codepen.io/3685267/timed-cards-3.jpg'
    },
    {
        place: 'Sierra Nevada - USA',
        title: 'YOSEMITE',
        title2: 'NATIONAL PARAK',
        description: 'Yosemite National Park is a showcase of the American wilderness, revered for its towering granite monoliths, ancient giant sequoias, and thundering waterfalls. The park offers year-round recreational activities, from rock climbing to serene valley walks.',
        image: 'https://assets.codepen.io/3685267/timed-cards-4.jpg'
    },
    {
        place: 'Tarifa - Spain',
        title: 'LOS LANCES',
        title2: 'BEACH',
        description: 'Los Lances Beach in Tarifa is a coastal paradise known for its consistent winds, making it a world-renowned spot for kitesurfing and windsurfing. The beach\'s long, sandy shores provide ample space for relaxation and sunbathing, with a vibrant atmosphere of beach bars and cafes.',
        image: '/shoesmith/resources/front/images/slider1.1.png'
    },
    {
        place: 'Cappadocia - Turkey',
        title: 'Göreme',
        title2: 'Valley',
        description: 'Göreme Valley in Cappadocia is a historical marvel set against a unique geological backdrop, where centuries of wind and water have sculpted the landscape into whimsical formations. The valley is also famous for its open-air museums, underground cities, and the enchanting experience of hot air ballooning.',
        image: 'https://assets.codepen.io/3685267/timed-cards-6.jpg'
    },
]

const _ = (id) => document.getElementById(id)
const cards = data.map((i, index) =>
    `<div class="card" id="card${index}" style="background-image:url(${i.image})"  ></div>`).join('')



const cardContents = data.map((i, index) => `<div class="card-content" id="card-content-${index}">
<div class="content-start"></div>
<div class="content-place">${i.place}</div>
<div class="content-title-1">${i.title}</div>
<div class="content-title-2">${i.title2}</div>

</div>`).join('')


const sildeNumbers = data.map((_, index) => `<div class="item" id="slide-item-${index}" >${index+1}</div>`).join('')
_('demo').innerHTML = cards + cardContents
_('slide-numbers').innerHTML = sildeNumbers


const range = (n) =>
    Array(n)
    .fill(0)
    .map((i, j) => i + j);
const set = gsap.set;

function getCard(index) {
    return `#card${index}`;
}

function getCardContent(index) {
    return `#card-content-${index}`;
}

function getSliderItem(index) {
    return `#slide-item-${index}`;
}

function animate(target, duration, properties) {
    return new Promise((resolve) => {
        gsap.to(target, {
            ...properties,
            duration: duration,
            onComplete: resolve,
        });
    });
}

let order = [0, 1, 2, 3, 4, 5];
let detailsEven = true;

let offsetTop = 200;
let offsetLeft = 700;
let cardWidth = 200;
let cardHeight = 300;
let gap = 40;
let numberSize = 50;
const ease = "sine.inOut";

function init() {
    const [active, ...rest] = order;
    const detailsActive = detailsEven ? "#details-even" : "#details-odd";
    const detailsInactive = detailsEven ? "#details-odd" : "#details-even";
    const {
        innerHeight: height,
        innerWidth: width
    } = window;
    offsetTop = height - 430;
    offsetLeft = width - 830;

    gsap.set("#pagination", {
        top: offsetTop + 330,
        left: offsetLeft,
        y: 200,
        opacity: 0,
        zIndex: 60,
    });
    gsap.set("nav", {
        y: -200,
        opacity: 0
    });

    gsap.set(getCard(active), {
        x: 0,
        y: 0,
        width: window.innerWidth,
        height: window.innerHeight,
    });
    gsap.set(getCardContent(active), {
        x: 0,
        y: 0,
        opacity: 0
    });
    gsap.set(detailsActive, {
        opacity: 0,
        zIndex: 22,
        x: -200
    });
    gsap.set(detailsInactive, {
        opacity: 0,
        zIndex: 12
    });
    gsap.set(`${detailsInactive} .text`, {
        y: 100
    });
    gsap.set(`${detailsInactive} .title-1`, {
        y: 100
    });
    gsap.set(`${detailsInactive} .title-2`, {
        y: 100
    });
    gsap.set(`${detailsInactive} .desc`, {
        y: 50
    });
    gsap.set(`${detailsInactive} .cta`, {
        y: 60
    });

    gsap.set(".progress-sub-foreground", {
        width: 500 * (1 / order.length) * (active + 1),
    });

    rest.forEach((i, index) => {
        gsap.set(getCard(i), {
            x: offsetLeft + 400 + index * (cardWidth + gap),
            y: offsetTop,
            width: cardWidth,
            height: cardHeight,
            zIndex: 30,
            borderRadius: 10,
        });
        gsap.set(getCardContent(i), {
            x: offsetLeft + 400 + index * (cardWidth + gap),
            zIndex: 40,
            y: offsetTop + cardHeight - 100,
        });
        gsap.set(getSliderItem(i), {
            x: (index + 1) * numberSize
        });
    });

    gsap.set(".indicator", {
        x: -window.innerWidth
    });

    const startDelay = 0.6;

    gsap.to(".cover", {
        x: width + 400,
        delay: 0.5,
        ease,
        onComplete: () => {
            setTimeout(() => {
                loop();
            }, 500);
        },
    });
    rest.forEach((i, index) => {
        gsap.to(getCard(i), {
            x: offsetLeft + index * (cardWidth + gap),
            zIndex: 30,
            delay: 0.05 * index,
            ease,
            delay: startDelay,
        });
        gsap.to(getCardContent(i), {
            x: offsetLeft + index * (cardWidth + gap),
            zIndex: 40,
            delay: 0.05 * index,
            ease,
            delay: startDelay,
        });
    });
    gsap.to("#pagination", {
        y: 0,
        opacity: 1,
        ease,
        delay: startDelay
    });
    gsap.to("nav", {
        y: 0,
        opacity: 1,
        ease,
        delay: startDelay
    });
    gsap.to(detailsActive, {
        opacity: 1,
        x: 0,
        ease,
        delay: startDelay
    });
}

let clicks = 0;

function step() {
    return new Promise((resolve) => {
        order.push(order.shift());
        detailsEven = !detailsEven;

        const detailsActive = detailsEven ? "#details-even" : "#details-odd";
        const detailsInactive = detailsEven ? "#details-odd" : "#details-even";

        document.querySelector(`${detailsActive} .place-box .text`).textContent =
            data[order[0]].place;
        document.querySelector(`${detailsActive} .title-1`).textContent =
            data[order[0]].title;
        document.querySelector(`${detailsActive} .title-2`).textContent =
            data[order[0]].title2;
        document.querySelector(`${detailsActive} .desc`).textContent =
            data[order[0]].description;

        gsap.set(detailsActive, {
            zIndex: 22
        });
        gsap.to(detailsActive, {
            opacity: 1,
            delay: 0.4,
            ease
        });
        gsap.to(`${detailsActive} .text`, {
            y: 0,
            delay: 0.1,
            duration: 0.7,
            ease,
        });
        gsap.to(`${detailsActive} .title-1`, {
            y: 0,
            delay: 0.15,
            duration: 0.7,
            ease,
        });
        gsap.to(`${detailsActive} .title-2`, {
            y: 0,
            delay: 0.15,
            duration: 0.7,
            ease,
        });
        gsap.to(`${detailsActive} .desc`, {
            y: 0,
            delay: 0.3,
            duration: 0.4,
            ease,
        });
        gsap.to(`${detailsActive} .cta`, {
            y: 0,
            delay: 0.35,
            duration: 0.4,
            onComplete: resolve,
            ease,
        });
        gsap.set(detailsInactive, {
            zIndex: 12
        });

        const [active, ...rest] = order;
        const prv = rest[rest.length - 1];

        gsap.set(getCard(prv), {
            zIndex: 10
        });
        gsap.set(getCard(active), {
            zIndex: 20
        });
        gsap.to(getCard(prv), {
            scale: 1.5,
            ease
        });

        gsap.to(getCardContent(active), {
            y: offsetTop + cardHeight - 10,
            opacity: 0,
            duration: 0.3,
            ease,
        });
        gsap.to(getSliderItem(active), {
            x: 0,
            ease
        });
        gsap.to(getSliderItem(prv), {
            x: -numberSize,
            ease
        });
        gsap.to(".progress-sub-foreground", {
            width: 500 * (1 / order.length) * (active + 1),
            ease,
        });

        gsap.to(getCard(active), {
            x: 0,
            y: 0,
            ease,
            width: window.innerWidth,
            height: window.innerHeight,
            borderRadius: 0,
            onComplete: () => {
                const xNew = offsetLeft + (rest.length - 1) * (cardWidth + gap);
                gsap.set(getCard(prv), {
                    x: xNew,
                    y: offsetTop,
                    width: cardWidth,
                    height: cardHeight,
                    zIndex: 30,
                    borderRadius: 10,
                    scale: 1,
                });

                gsap.set(getCardContent(prv), {
                    x: xNew,
                    y: offsetTop + cardHeight - 100,
                    opacity: 1,
                    zIndex: 40,
                });
                gsap.set(getSliderItem(prv), {
                    x: rest.length * numberSize
                });

                gsap.set(detailsInactive, {
                    opacity: 0
                });
                gsap.set(`${detailsInactive} .text`, {
                    y: 100
                });
                gsap.set(`${detailsInactive} .title-1`, {
                    y: 100
                });
                gsap.set(`${detailsInactive} .title-2`, {
                    y: 100
                });
                gsap.set(`${detailsInactive} .desc`, {
                    y: 50
                });
                gsap.set(`${detailsInactive} .cta`, {
                    y: 60
                });
                clicks -= 1;
                if (clicks > 0) {
                    step();
                }
            },
        });

        rest.forEach((i, index) => {
            if (i !== prv) {
                const xNew = offsetLeft + index * (cardWidth + gap);
                gsap.set(getCard(i), {
                    zIndex: 30
                });
                gsap.to(getCard(i), {
                    x: xNew,
                    y: offsetTop,
                    width: cardWidth,
                    height: cardHeight,
                    ease,
                    delay: 0.1 * (index + 1),
                });

                gsap.to(getCardContent(i), {
                    x: xNew,
                    y: offsetTop + cardHeight - 100,
                    opacity: 1,
                    zIndex: 40,
                    ease,
                    delay: 0.1 * (index + 1),
                });
                gsap.to(getSliderItem(i), {
                    x: (index + 1) * numberSize,
                    ease
                });
            }
        });
    });
}

async function loop() {
    await animate(".indicator", 2, {
        x: 0
    });
    await animate(".indicator", 0.8, {
        x: window.innerWidth,
        delay: 0.3
    });
    set(".indicator", {
        x: -window.innerWidth
    });
    await step();
    loop();
}

async function loadImage(src) {
    return new Promise((resolve, reject) => {
        let img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}

async function loadImages() {
    const promises = data.map(({
        image
    }) => loadImage(image));
    return Promise.all(promises);
}

async function start() {
    try {
        await loadImages();
        init();
    } catch (error) {
        console.error("One or more images failed to load", error);
    }
}

start()
</script>