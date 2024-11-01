<div class="wpb-field">
  <h2 class="text-sm font-medium text-gray-900">
      <?= __('Customizations:', 'wpboutik') ?>
  </h2>
  <div class="wpb-field wpb-field-price hidden">
      <label for="gift_card_price">
          <?= __('Custom price', 'wpboutik') ?>
      </label>
          <input name="gift_card_price" id="gift_card_price" type="number" min="1" class="customizable_fields" />
  </div>
  <div class="wpb-field">
      <label for="gift_card_mail">
          <?= __('Recipient\'s email', 'wpboutik') ?>
      </label>
        <input name="gift_card_mail" id="gift_card_mail" type="email" class="customizable_fields " />
  </div>
  <div class="wpb-field">
    <label for="gift_card_message">
        <?= __('Your message:', 'wpboutik') ?>
    </label>
    <textarea name="gift_card_message" id="gift_card_message" class="customizable_fields"></textarea>
  </div>
</div>