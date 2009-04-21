{START_FORM}

<div class="padded">
    <strong>{TITLE_LABEL}</strong><br />{TITLE}
    <!-- BEGIN title-error --><div class="error">{TITLE_ERROR}</div><!-- END title-error -->
</div>
<div class="padded">
    <strong>{MODE_LABEL}</strong><br />
    {MODE_1} {MODE_1_LABEL}<br />
    {MODE_2} {MODE_2_LABEL}<br />
    {MODE_3} {MODE_3_LABEL} {CURRENT_PHOTO}<br />
    <!-- BEGIN mode_4 -->{MODE_4} {MODE_4_LABEL}<br /><!-- END mode_4 -->
    <!-- BEGIN mode_5 -->{MODE_5} {MODE_5_LABEL} *<br /><!-- END mode_5 -->
    <!-- BEGIN mode_6 -->{MODE_6} {MODE_6_LABEL} *<!-- END mode_6 -->
    <!-- BEGIN flickr_set --><div style="margin-left:25px">* {FLICKR_SET_LABEL} {FLICKR_SET}</div><!-- END flickr_set -->
    <!-- BEGIN mode-error --><div class="error">{MODE_ERROR}</div><!-- END mode-error -->
</div>
<div class="padded">
    <strong>{RESIZE_LABEL}</strong><br />{TN_WIDTH} X {TN_HEIGHT} {PIXELS_LABEL}
    <!-- BEGIN resize-error --><div class="error">{RESIZE_ERROR}</div><!-- END resize-error -->
</div>
<div class="padded"><strong>{TEMPLATE_LABEL}</strong><br />{TEMPLATE}</div>
<div class="padded">{SUBMIT} {CANCEL}</div>

{END_FORM}
