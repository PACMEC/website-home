<?php

function shop_list_item_theme($product)
{
  return \get_template_part("template-parts/components/store-item", ["product" => $product]);
}
