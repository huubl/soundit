<div class="technology-background">
    <video src="@field('technology_background', 'url')" loop autoplay muted>
</div>

<div class="row expanded">
    <div class="column small-10 small-offset-1 xxlarge-6 xxlarge-offset-0">
        <div class="technology-wrapper">
            <span class="heading-three-white">@field('technology_uppertitle')</span>
            <h2 class="heading-two-white">@field('technology_title')</h2>
            <p>@field('technology_text')</p>

            <div class="technology__image">
                <img src="@field('technology_image', 'url')" alt="">
            </div>

            @hasfield('technology_characteristics')
            <ul class="technology__characteristics">
            @fields('technology_characteristics')
            <li class="btn">
                    @group('characteristic')
                    <h2 class="heading-two-grey">@sub('title')</h2>
                    <span class="heading-three-grey">@sub('subtitle')</span>
                    @endgroup 
                </li>
                @endfields
            </ul>
            @endfield
            
        </div>
    </div>
</div>