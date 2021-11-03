<!DOCTYPE html>
<html <?= \language_attributes(); ?>>
  <head>
      <meta charset="<?= \pageinfo( 'charset' ); ?>">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <?php \pacmec_head(); ?>
  </head>

<body class="<?= \isStore() ? "page-shop page-shop-v2" : (\isAbout() ? "about-page" : ""); ?>">
      <header id="main-header" class="header-default hidden-sm hidden-xs">
          <div class="container">
              <div class="inner">
                  <div class="logo">
                      <a href="<?= \siteinfo('url'); ?>" title="logo">
                        <!-- <img alt="logo-theme" src="<?= \folder_theme("default")."/assets/"; ?>images/logo.png" class="img-responsive" /> -->
                        <center>
                          <img style="height:50px;" alt="logo-theme" src="<?= \siteinfo("logo"); ?>" class="img-responsive" />
                        </center>
                      </a>
                  </div>
                  <div class="main-menu">
                      <nav class="navbar collapse navbar-collapse">
                        <?php
                          $menu = \pacmec_load_menu("primary");
                          $a = "";
                          if($menu !== false){
                            foreach ($menu->items As $key => $item) {
                              $a .= \PACMEC\Util\Html::tag('li',
                                \PACMEC\Util\Html::tag('a',
                                  \PACMEC\Util\Html::tag('i', '', [$item->icon]) . $item->title
                                , [], [
                                  "href" => \__url($item->tag_href),
                                ])
                              , "level1");
                            }
                          }
                          echo \PACMEC\Util\Html::tag("ul", $a, ['nav navbar-nav'], []);
                          ?>
                      </nav>
                  </div>
                  <div class="header-right">
                      <div class="search-popup search_modal search">
                          <a href="#" class="tp_btn_search" data-toggle="modal" data-target=".mymodal">
                              <i class="pe-7s-search"></i>
                          </a>
                      </div>

                      <!-- Start Shopping cart -->
                      <div id="cart_top">
                          <div class="shopping-cart">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" >
                                  <span class="text-cart"><?= \__at("shoppings_cart"); ?></span>
                                  <!-- <span class="item">(3)</span> -->
                              </a>
                              <div class="dropdown-menu dropdown-cart">
                                  <ul class="mini-products-list">
                                      <li class="item-cart">
                                          <div class="product-details">
                                              <div class="product-img-wrap">
                                                  <img src="<?= \folder_theme("default")."/assets/"; ?>images/product/img-pro1.jpg" alt="" class="img-reponsive">
                                              </div>
                                              <div class="inner-left">
                                                  <div class="product-name"><a href="#">Harman Kardon Onyx Studio </a></div>
                                                  <div class="product-price">
                                                      $ 60.00 <span>( x1)</span>
                                                  </div>
                                              </div>
                                          </div>
                                          <a href="#" class="close"><i class="ion-ios-close-empty"></i></a>
                                      </li>
                                      <li class="item-cart">
                                          <div class="product-details">
                                              <div class="product-img-wrap">
                                                  <img src="<?= \folder_theme("default")."/assets/"; ?>images/product/img-pro2.jpg" alt="" class="img-reponsive">
                                              </div>
                                              <div class="inner-left">
                                                  <div class="product-name"><a href="#">Harman Kardon Onyx Studio </a></div>
                                                  <div class="product-price">
                                                      $ 60.00 <span>( x2)</span>
                                                  </div>
                                              </div>
                                          </div>
                                          <a href="#" class="close"><i class="ion-ios-close-empty"></i></a>
                                      </li>
                                  </ul>
                                  <div class="bottom-cart">
                                      <div class="cart-price">
                                          <strong>Subtotal: </strong>
                                          <span class="price-total">$ 180.00</span>
                                      </div>
                                      <div class="button-cart">
                                          <span class="btn-outline btn-small viewcart">
                                              <a href="#" title="">View Cart</a>
                                          </span>
                                          <span class="btn-theme btn-small checkout">
                                              <a href="#" title="">Checkout</a>
                                          </span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- End Shopping cart -->

                  </div>
              </div>
          </div>
      </header>
      <header id="header_mobile" class="header-mobile-default hidden-lg hidden-md">
          <div class="header-top">
              <div class="container">
                  <div class="logo text-center">
                      <a href="#" title="logo"><img alt="logo-theme" src="<?= \folder_theme("default")."/assets/"; ?>images/logo.png" class="img-responsive"></a>
                  </div>
              </div>
          </div>
          <div class="header-bottom border-bottom">
              <div class="container">
                  <div class="inner">
                      <div class="header-main">
                          <div class="main-left">
                              <button data-toggle="offcanvas" class="btn btn-offcanvas btn-toggle-canvas offcanvas" type="button">
                                 <i class="ion ion-android-menu"></i>
                              </button>
                          </div>
                          <div class="main-right">
                              <div  class="search-popup search_modal search">
                                  <a href="#" class="tp_btn_search" data-toggle="modal" data-target="#Searchmobile">
                                      <i class="pe-7s-search"></i>
                                  </a>
                                  <div id="Searchmobile" class="modal fade" role="dialog">
                                      <div class="modal-dialog modal-lg">
                                          <div class="modal-content">
                                              <button type="button" class="close" data-dismiss="modal"><span class="pe-7s-close"></span></button>

                                              <form method="get" class="searchform" action="/home-v1.html">
                                                  <div class="pbr-search input-group">
                                                      <input name="search" maxlength="40" class="form-control input-large input-search" size="20" placeholder="Search…" type="text">
                                                      <span class="input-group-addon input-large btn-search">
                                                          <input value="" type="submit">
                                                      </span>
                                                  </div>
                                              </form>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- Start Shopping cart -->
                              <div class="shopping-cart">
                                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                      <span class="text-cart">Cart</span>
                                      <span class="item">(3)</span>
                                  </a>
                                  <div class="dropdown-menu dropdown-cart">
                                      <ul class="mini-products-list">
                                          <li class="item-cart">

                                              <div class="product-details">
                                                  <div class="product-img-wrap">
                                                      <img src="<?= \folder_theme("default")."/assets/"; ?>images/product/img-pro1.jpg" alt="" class="img-reponsive">
                                                  </div>
                                                  <div class="inner-left">
                                                      <div class="product-name"><a href="#">Harman Kardon Onyx Studio </a></div>
                                                      <div class="product-price">
                                                          $ 60.00 <span>( x1)</span>
                                                      </div>
                                                  </div>
                                              </div>
                                              <a href="#" class="close"><i class="ion-ios-close-empty"></i></a>
                                          </li>
                                          <li class="item-cart">
                                              <div class="product-details">
                                                  <div class="product-img-wrap">
                                                      <img src="<?= \folder_theme("default")."/assets/"; ?>images/product/img-pro2.jpg" alt="" class="img-reponsive">
                                                  </div>
                                                  <div class="inner-left">
                                                      <div class="product-name"><a href="#">Harman Kardon Onyx Studio </a></div>
                                                      <div class="product-price">
                                                          $ 60.00 <span>( x2)</span>
                                                      </div>
                                                  </div>
                                              </div>
                                              <a href="#" class="close"><i class="ion-ios-close-empty"></i></a>
                                          </li>
                                      </ul>
                                      <div class="bottom-cart">
                                          <div class="cart-price">
                                              <strong>Subtotal: </strong>
                                              <span class="price-total">$ 180.00</span>
                                          </div>
                                          <div class="button-cart">
                                              <span class="btn-outline btn-small viewcart">
                                                  <a href="#" title="">View Cart</a>
                                              </span>
                                              <span class="btn-theme btn-small checkout">
                                                  <a href="#" title="">Checkout</a>
                                              </span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <!-- End Shopping cart -->
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </header>
