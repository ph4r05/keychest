<table class="panel" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="panel-content panel-{{ $type or 'default' }}">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="panel-item">
                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
