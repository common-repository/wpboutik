export default function show_button_cart (metas) {
  if(metas == undefined)
    return true;
  let show = true;
  if (metas.gestion_stock == 1) {
    if (metas.continu_rupture == 1)
      return show;
    if (!!metas?.variants && metas.type != 'gift_card') {
      if (metas.variants != '[]') {
        const variants_list = JSON.parse(metas.variants);
        show = false;
        for (let variant of variants_list) {
          if (+(variant.quantity) !== 0) {
            show = true; break;
          }
        }
      }
    } else {
      (metas.quantity == 0)
        show = false
    }
  }
  return show;
}