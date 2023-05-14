<div class="text-center page-footer">
    <div class="footer-inner login-page-footer">
        @if(config('branding.name')=="lanes")
            <span class="with_img">            
                <img src="{{ get_brand_setting('logo.footer_logo') }}" alt="Lanes group logo" width="50">
                <span>
                    &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/">{{ get_brand_setting('footer_text_part2') }}</a>
                </span>
            </span>
        @else
            <span>
                &copy; {{ date('Y') }} {{ get_brand_setting('footer_text') }} <a href="https://www.imastr.com/" target="_blank" rel="noopener noreferrer">{!! get_brand_setting('footer_text_part2') !!}</a>
            </span>
        @endif
        <div class="scroll-to-top" style="display: none;">
            <i class="icon-arrow-up"></i>
        </div>
    </div>
</div> 