export function ShowPrice (metas) {
  const {variants, price, price_before_reduction} = metas;
  return (<>
    {
      (!!price_before_reduction) && 
      <span class="current_price_before_reduction">
        <span>
          {number_format(price_before_reduction)}
        </span>
      </span>
    }
    <span class="current_price">
      {
        (variants != '[]' && !!variants) ?
          (show_variant_price(variants)) ? show_variant_price(variants) : number_format(price)
        :
          number_format(price)
      }
    </span>
  </>)
}

function number_format (number) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(number)
}

function show_variant_price (variants) {
  let min = false;
  let max = false;
  const variants_list = JSON.parse(variants);
  for (let variation of variants_list) {
    if (variation.status == 1) {
      if ((variation?.price && variation.price < min) || !min) {
        min = variation.price;
      }
      if ((variation?.price && variation.price > max) || !max) {
        max = variation.price;
      }
    }
  }
  return (min != max) ? number_format(min)+' - '+number_format(max) : number_format(min);
}