@props([
    'title' => '',
    'icon' => 'ki-chart-simple',
    'iconColor' => 'primary',
    'status' => null,
    'statusText' => '',
    'statusColor' => 'warning',
    'actions' => []
])

<div class="col-xl-6">
    <div class="card card-flush h-md-100">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <div class="d-flex align-items-center">
                    <i class="ki-duotone {{ $icon }} fs-2x text-{{ $iconColor }} me-3">
                        @foreach(explode(' ', $icon) as $path)
                            <span class="path{{ $loop->iteration }}"></span>
                        @endforeach
                    </i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title }}</span>
                </div>
            </div>
        </div>
        <div class="card-body pt-2 pb-4">
            <div class="d-flex flex-column">
                @if($status !== null)
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge badge-light-{{ $statusColor }} me-2">
                            {{ $statusText }}
                        </span>
                        <span class="fs-6 text-gray-600">حالة العملية</span>
                    </div>
                @endif

                @if(!empty($actions))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($actions as $action)
                            @if(isset($action['type']) && $action['type'] === 'ajax')
                                <button type="button"
                                        class="btn btn-sm btn-{{ $action['color'] ?? 'primary' }} {{ $action['class'] ?? '' }} admin-action-btn"
                                        data-action="{{ $action['url'] }}"
                                        data-method="{{ $action['method'] ?? 'POST' }}"
                                        data-confirm="{{ $action['confirm'] ?? false }}"
                                        data-confirm-text="{{ $action['confirm_text'] ?? '' }}"
                                        @if(isset($action['disabled']) && $action['disabled']) disabled @endif>
                                    @if(isset($action['icon']))
                                        <i class="ki-duotone {{ $action['icon'] }} fs-4 me-1">
                                            @foreach(explode(' ', $action['icon']) as $path)
                                                <span class="path{{ $loop->iteration }}"></span>
                                            @endforeach
                                        </i>
                                    @endif
                                    {{ $action['text'] }}
                                </button>
                            @elseif(isset($action['type']) && $action['type'] === 'modal')
                                <a href="#"
                                   class="has_action btn btn-sm btn-{{ $action['color'] ?? 'primary' }} {{ $action['class'] ?? '' }}"
                                   data-type="{{ $action['modal_type'] ?? 'show' }}"
                                   data-action="{{ $action['url'] }}">
                                    @if(isset($action['icon']))
                                        <i class="ki-duotone {{ $action['icon'] }} fs-4 me-1">
                                            @foreach(explode(' ', $action['icon']) as $path)
                                                <span class="path{{ $loop->iteration }}"></span>
                                            @endforeach
                                        </i>
                                    @endif
                                    {{ $action['text'] }}
                                </a>
                            @elseif(isset($action['type']) && $action['type'] === 'delete')
                                <a href="#"
                                   class="delete_btn btn btn-sm btn-{{ $action['color'] ?? 'danger' }} {{ $action['class'] ?? '' }}"
                                   data-action="{{ $action['url'] }}">
                                    @if(isset($action['icon']))
                                        <i class="ki-duotone {{ $action['icon'] }} fs-4 me-1">
                                            @foreach(explode(' ', $action['icon']) as $path)
                                                <span class="path{{ $loop->iteration }}"></span>
                                            @endforeach
                                        </i>
                                    @endif
                                    {{ $action['text'] }}
                                </a>
                            @else
                                <button type="button"
                                        class="btn btn-sm btn-{{ $action['color'] ?? 'primary' }} {{ $action['class'] ?? '' }}"
                                        onclick="{{ $action['onclick'] }}"
                                        @if(isset($action['disabled']) && $action['disabled']) disabled @endif>
                                    @if(isset($action['icon']))
                                        <i class="ki-duotone {{ $action['icon'] }} fs-4 me-1">
                                            @foreach(explode(' ', $action['icon']) as $path)
                                                <span class="path{{ $loop->iteration }}"></span>
                                            @endforeach
                                        </i>
                                    @endif
                                    {{ $action['text'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
