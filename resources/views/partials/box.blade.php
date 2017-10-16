<div class="box box-primary {{ isset($boxCss) ? $boxCss : '' }}"
@if(isset($boxId))
    id="{{ $boxId }}"
@endif
>
    <div class="box-header with-border {{ isset($headerCss) ? $headerCss : '' }}">
        <h3 data-widget="{{ isset($collapseHeader) ? 'collapse' : '' }}"
            class="box-title {{ isset($h3Css) ? $h3Css : '' }}"
        >{{ isset($title) ? $title : '' }}</h3>

    @if (!isset($collapsible) || $collapsible === true)
        <div class="box-tools pull-right">
            <button type="button" data-widget="collapse" data-toggle="tooltip"
                    title="" class="btn btn-box-tool" data-original-title="Collapse"
            >
            @if (isset($collapsed) && $collapsed)
            <i class="fa fa-plus"></i>
            @else
            <i class="fa fa-minus"></i>
            @endif
            </button>

        </div>
    @endif

    </div>
    <div class="box-body">
        {{ $slot }}
    </div>
</div>
