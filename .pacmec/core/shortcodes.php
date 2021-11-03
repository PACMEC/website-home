<?php
/**
 * @package    PACMEC
 * @category   Shortcodes
 * @copyright  2021 FelipheGomez
 * @author     FelipheGomez <info@pacmec.co>
 * @license    license.txt
 * @version    1.0.0
 */
function pacmec_page_home($atts=[], $content="")
{
  return \PACMEC\Util\Html::tag('div', \get_template_part("template-parts/pages/home", array_merge([
    'content' => $content,
    // More args
  ], $args)), []);
}
\add_shortcode('home', 'pacmec_page_home');

function component_slider_products($atts=[], $content="")
{
  $args = \shortcode_atts([
    'slogan' => false,
    'page' => 1,
    'limit' => 25,
    'categories' => "",
  ], $atts);
  $args['categories'] = array_filter(explode(',', $args['categories']));
  return \PACMEC\Util\Html::tag('div', \get_template_part("template-parts/components/slider-products", array_merge([
    'content' => $content,
    // More args
  ], $args)), []);
}
\add_shortcode('slider-products', 'component_slider_products');

function pacmec_menu_icons($atts=[], $content="")
{
  $single_view = "";
  $args = \shortcode_atts([
    'menu_slug' => "icons_primary",
  ], $atts);
  return \PACMEC\Util\Html::tag('div', \get_template_part('template-parts/components/menu-icons', array_merge([
    "content" => $content,
    "menu_slug" => $args['menu_slug'],
  ], $args)), []);
}
\add_shortcode('menu-icons', 'pacmec_menu_icons');

function pacmec_store_lastest($atts=[], $content="")
{
  $args = \shortcode_atts([
  ], $atts);
  return \PACMEC\Util\Html::tag('div', \get_template_part('template-parts/components/store-lastest', array_merge([
    "content" => $content,
  ], $args)), []);
}
\add_shortcode('store-lastest', 'pacmec_store_lastest');

function pacmec_block_video($atts=[], $content="")
{
  $args = \shortcode_atts([
    "imgback" => \folder_theme("default")."/assets/images/banner/1.jpg",
    "videoid" => "YlpQvy1xXJk"
  ], $atts);
  return \PACMEC\Util\Html::tag('div', \get_template_part('template-parts/components/block-video', array_merge([
    "content" => $content,
  ], $args)), []);
}
\add_shortcode('block-video', 'pacmec_block_video');

function pacmec_form_signin($atts=[], $content="")
{
  global $PACMEC;
  $args = \shortcode_atts([
    'redirect' => false
  ], $atts);
  $is_error    = null;
  $msg         = null;
  $form_slug = "signin-pacmec";
  $result_captcha = \pacmec_captcha_check($form_slug);
  $form = new \PACMEC\Form\Form(
    ''
    , 'POST'
    , \PACMEC\Form\FormType::Horizontal
    , 'Error:'
    , "OK"
    , ['class'=>'pacmec-row row']);
  $form->setWidths(12,12);
  $form->setGlobalValidations([
    new \PACMEC\Form\Validation\LambdaValidation(\__at("form_r_invalid_info"), function () use ($PACMEC, $form_slug, $result_captcha, $form) {
      if(!isset($PACMEC['input']["adcopy_response"]) && ($result_captcha !== 'captcha_disabled')) return false;
      switch ($result_captcha) {
        case 'captcha_r_success':
        case 'captcha_disabled':
          if(isset($PACMEC['input']["submit-{$form_slug}"]) && isset($PACMEC['input']['nick']) && isset($PACMEC['input']['hash'])){
            $r_login = $PACMEC['session']->login([
              'nick' => $PACMEC['input']['nick'],
              'hash' => $PACMEC['input']['hash']
            ]);
            switch ($r_login) {
              case 'no_exist':
                $form->setErrorMessage(__at('signin_r_no_exist'));
                return false;
                break;
              case 'inactive':
                $form->setErrorMessage(__at('signin_r_inactive'));
                return false;
                break;
              case 'error':
                $form->setErrorMessage(__at('signin_r_error'));
                return false;
                break;
              case 'success':
                $form->setSucessMessage(__at('signin_r_success'));
                $url = (isset($PACMEC['input']['redirect'])) ? ($PACMEC['input']['redirect']) : siteinfo('siteurl').__url_s("/%pacmec_meaccount%");
                echo "<meta http-equiv=\"refresh\" content=\"0;URL='{$url}'\" />";
                return true;
                break;
              case 'invalid_credentials':
                $form->setErrorMessage(__at('signin_r_invalid_credentials'));
                return false;
                break;
              default:
                $form->setErrorMessage(__at('undefined'));
                return false;
                break;
            }
          } else {
            $form->setErrorMessage(__at('signin_r_invalid_info'));
            return false;
          }
          break;
        default:
          $form->setErrorMessage(__at($result_captcha));
          return false;
          break;
      }
      return true;
    })
  ]);
  $form->hidden([
    [
      "name"  => "redirect",
      "value" => ($args['redirect']==false) ? siteinfo('siteurl').__url_s("/%pacmec_meaccount%") : urldecode($args['redirect'])
    ]
  ]);
  $form->addFieldWithLabel(
    \PACMEC\Form\Text::withNameAndValue('nick', '', NULL, [
      new \PACMEC\Form\Validation\RequiredValidation(__at('required_field'))
      , new \PACMEC\Form\Validation\MinLengthValidation(4)
    ], [], true)
    , __at('field_username')
    , ''
    , ['pacmec-col m12 l12']
  );
  $form->addFieldWithLabel(
    \PACMEC\Form\Password::withNameAndValue('hash', '', 32, [
      new \PACMEC\Form\Validation\RequiredValidation(__at('required_field'))
      , new \PACMEC\Form\Validation\MinLengthValidation(4)
    ], [], true)
    , \__at('field_hash')
    , ''
    , ['pacmec-col m12 l12']
  );
  $form->Code .= \PACMEC\Util\Html::tag('div', "<br/>".\pacmec_captcha_widget_html("pacmec-captcha-".\randString(11)."-login", $form_slug, 'custom-pacmec'), ['single-input-item mb-3']);
  $form->addSubmitButton(__at('signin_btn'), [
    'name'=>"submit-{$form_slug}",
    "class" => 'pacmec-button pacmec-green pacmec-round-large w-100'
  ], ['btn']);

  $form->Code .= '
    <div class="pacmec-col m12 l12 pacmec-center">
      <a href="'.siteinfo('siteurl')."/{$GLOBALS['PACMEC']['permanents_links']['%pacmec_forgotten_password%']}".'" class="forget-pwd mb-3">'.__at('forgot_password').'</a>
    </div>';
  return isGuest() ? \PACMEC\Util\Html::tag('div', $form, ['col-lg-12'], []) : '';
}
\add_shortcode('pacmec-form-signin', 'pacmec_form_signin');
