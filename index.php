<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/ProductPdo.php';
require './pdos/OrderPdo.php';
require './pdos/SearchPdo.php';
require './pdos/SellerPdo.php';
require './pdos/ReviewPdo.php';
require './pdos/JWTPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   JWT   ****************** */
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

    /* ******************   User   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('POST', '/login', ['IndexController', 'login']); // 로그인
    $r->addRoute('POST', '/logout', ['IndexController', 'logout']); // 로그아웃
    $r->addRoute('GET', '/login/jwt', ['IndexController', 'loginByJwt']); // 자동 로그인
    $r->addRoute('POST', '/users', ['IndexController', 'createUser']); // 회원 가입
    $r->addRoute('GET', '/users', ['IndexController', 'getUserInfo']); // 사용자 정보 조회
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
    $r->addRoute('PATCH', '/users', ['IndexController', 'updateUserInfo']); // 사용자 정보 수정
    $r->addRoute('DELETE', '/users', ['IndexController', 'deleteUser']); // 회원 탈퇴

    /* ******************   Product   ****************** */
    $r->addRoute('GET', '/products/home', ['ProductController', 'getHome']); // 홈 화면 조회
    $r->addRoute('GET', '/products/top', ['ProductController', 'getTopProducts']); // 인기 작품 목록 조회
    $r->addRoute('GET', '/products/latest', ['ProductController', 'getLatestProduct']); // 최신 작품 목록 조회
    $r->addRoute('GET', '/products/recommendation', ['ProductController', 'getRecommendation']); // 사용자별 추천 작품 목록 조회
    $r->addRoute('PATCH', '/products/{productIdx}/star', ['ProductController', 'starProduct']); // 작품 즐겨찾기 등록
    $r->addRoute('GET', '/products/{productIdx}', ['ProductController', 'getProductDetail']); // 작품 상세 페이지 조회
    $r->addRoute('GET', '/products/{productIdx}/options', ['ProductController', 'getOption']); // 작품 옵션 조회
    $r->addRoute('GET', '/reviews/latest', ['ProductController', 'getLatestReview']); // 실시간 후기 목록 조회
    $r->addRoute('GET', '/orders/latest', ['ProductController', 'getLatestOrder']); // 실시간 구매 목록 조회

    /* ******************   Seller   ****************** */
    $r->addRoute('GET', '/sellers/top', ['SellerController', 'getTopSellers']); // 인기 작가 목록 조회
    $r->addRoute('PATCH', '/sellers/favorite/{sellerIdx}', ['SellerController', 'likeSeller']); // 좋아하는 작가 등록
    $r->addRoute('GET', '/sellers/{sellerIdx}', ['SellerController', 'getSellerInfo']); // 작가 프로필 조회

    /* ******************   Order   ****************** */
    $r->addRoute('PATCH', '/addresses/{addressIdx}', ['OrderController', 'updateAddressInfo']); // 배송지 정보 수정
    $r->addRoute('POST', '/orders/{productIdx}', ['OrderController', 'createOrder']); // 작품 구매
    $r->addRoute('DELETE', '/orders/{orderIdx}', ['OrderController', 'deleteOrder']); // 작품 구매 취소
    $r->addRoute('GET', '/orders', ['OrderController', 'getOrderList']); // 구매한 작품 목록 조회
    $r->addRoute('POST', '/orders/{orderIdx}/change', ['OrderController', 'changeOrder']); // 교환 신청
    $r->addRoute('POST', '/orders/{orderIdx}/refund', ['OrderController', 'refundOrder']); // 환불 신청
    $r->addRoute('PATCH', '/orders/{orderIdx}/change', ['OrderController', 'updateChangeRequest']); // 교환 신청 승인/거부
    $r->addRoute('PATCH', '/orders/{orderIdx}/refund', ['OrderController', 'updateRefundRequest']); // 환불 신청 승인/거부

    /* ******************   Review   ****************** */
    $r->addRoute('POST', '/reviews/comments/{reviewIdx}', ['ReviewController', 'createReviewComment']); // 후기 댓글 등록
    $r->addRoute('DELETE', '/reviews/comments/{commentIdx}', ['ReviewController', 'deleteReviewComment']); // 후기 댓글 삭제
    $r->addRoute('POST', '/orders/{orderIdx}/review', ['ReviewController', 'createReview']); // 후기 등록
    $r->addRoute('PATCH', '/reviews/{reviewIdx}', ['ReviewController', 'editReview']); // 후기 수정
    $r->addRoute('DELETE', '/reviews/{reviewIdx}', ['ReviewController', 'deleteReview']); // 후기 삭제
    $r->addRoute('GET', '/products/{productIdx}/reviews', ['ReviewController', 'getReviews']); // 작품 후기 목록 조회

    /* ******************   Search   ****************** */
    $r->addRoute('GET', '/products', ['SearchController', 'searchKeyword']); // 작품 검색
    $r->addRoute('GET', '/searches/top', ['SearchController', 'getLatestSearch']); // 최근 검색어 및 인기 검색어 조회

//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'OrderController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/OrderController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'SellerController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SellerController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;

//            case 'SearchController':
//                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
//                require './controllers/SearchController.php';
//                break;
//            case 'ReviewController':
//                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
//                require './controllers/ReviewController.php';
//                break;
//            case 'ElementController':
//                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
//                require './controllers/ElementController.php';
//                break;
//            case 'AskFAQController':
//                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
//                require './controllers/AskFAQController.php';
//                break;
        }

        break;
}
