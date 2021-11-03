<?php
\get_header();
if(\route_active())
{
  \get_template_part("template-parts/{$GLOBALS['PACMEC']['route']->layout}");
}
else
{ \get_template_part( 'template-parts/pages/error' ); }
\get_footer();
