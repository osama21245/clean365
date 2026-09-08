<div class="accordion mb-30" id="accordionExample">
    @if($faqs->count() < 1)
        <img src="{{asset('public/assets/admin-module/img/icons/faq.png')}}" class="mb-4"
             alt="{{ translate('faq') }}">
        <h3 class="text-muted">{{translate('no_faq_added_yet')}}</h3>
    @endif
    @foreach($faqs as $faq)
        <form action="{{route('admin.faq.update',[$faq->id])}}" method="POST" class="mb-30 hide-div"
              id="edit-{{$faq->id}}">
            @csrf
            @method('PUT')
            <div class="bg-light rounded p-xxl-4 p-3">
                <h4 class="mb-3">{{translate('Edit FAQ')}}</h4>
                <div class="row g-3">
                    <div class="col-12">
                        @include('partials._form-field', [
                            'type'        => 'text',
                            'name'        => 'question',
                            'id'          => 'edit_question_'.$faq->id,
                            'label'       => translate('Question'),
                            'placeholder' => translate('Enter Question'),
                            'icon'        => 'help',
                            'required'    => true,
                            'maxlength'   => 255,
                            'charCount'   => true,
                            'value'       => $faq->question,
                            'wrapClass'   => 'mb-0'])
                    </div>
                    <div class="col-12">
                        @include('partials._form-field', [
                            'type'        => 'textarea',
                            'name'        => 'answer',
                            'id'          => 'edit_answer_'.$faq->id,
                            'label'       => translate('Answer'),
                            'placeholder' => translate('Enter Answer'),
                            'rows'        => 4,
                            'maxlength'   => 1000,
                            'charCount'   => true,
                            'value'       => $faq->answer,
                            'wrapClass'   => 'mb-0'])
                    </div>
                </div>
            </div>
            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                <button type="button" class="btn btn--primary demo_check service-faq-update"
                        data-id="edit-{{$faq->id}}">
                    {{translate('Update FAQ')}}
                </button>
            </div>
        </form>

        <div class="accordion-item">
            <div class="accordion-header d-flex flex-wrap flex-sm-nowrap gap-3"
                 id="headingOne">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq_{{$faq->id}}"
                        aria-expanded="false" aria-controls="{{$faq->id}}">
                    {{$faq->question}}
                </button>
                <div class="btn-group d-flex gap-3 align-items-center">
                    <div>
                        @can('service_manage_status')
                        <label class="switcher" data-bs-toggle="modal" data-bs-target="#deactivateAlertModal">
                            <input class="switcher_input service-ajax-status-update" type="checkbox" {{$faq->is_active?'checked':''}}
                            data-route="{{route('admin.faq.status-update',[$faq->id])}}"
                                   data-id="faq-list">
                            <span class="switcher_control"></span>
                        </label>
                            @endcan
                    </div>
                    @can('service_update')
                    <button type="button"
                            data-id="{{$faq->id}}"
                            class="accordion-edit-btn bg-transparent border-0 p-0 show-service-edit-section">
                        <span class="material-icons">border_color</span>
                    </button>
                    @endcan
                    @can('service_delete')
                    <button type="button"
                            class="accordion-delete-btn bg-transparent border-0 p-0 faq-list-ajax-delete"
                            data-route="{{route('admin.faq.delete',[$faq->id,$faq->service_id])}}">
                        <span class="material-icons">delete</span>
                    </button>
                        @endcan
                </div>
            </div>
            <div id="faq_{{$faq->id}}" class="accordion-collapse collapse"
                 aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    {{$faq->answer}}
                </div>
            </div>
        </div>
    @endforeach
</div>

@once
    <script>
        $(".faq-list-ajax-delete").on('click', function (){
            let route = $(this).data('route');
            ajax_delete(route)
        })

        $(".service-faq-update").on('click', function (){
            let id = $(this).data('id');
            ajax_post(id)
        })

        $(".show-service-edit-section").on('click', function (){
            let id = $(this).data('id');
            $(`#edit-${id}`).toggle();
        })
    </script>
@endonce


