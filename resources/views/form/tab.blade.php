<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        @foreach($tabObj->getTabs() as $tab)
            <li class="nav-item">
                <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="#tab-{{ $tab['id'] }}" data-bs-toggle="tab">
                    {{ $tab['title'] }} <i class="fa fa-exclamation-circle text-danger d-none"></i>
                </a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content fields-group">
        @foreach($tabObj->getTabs() as $tab)
            <div class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}" id="tab-{{ $tab['id'] }}">
                @foreach($tab['fields'] as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>
        @endforeach
    </div>
</div>