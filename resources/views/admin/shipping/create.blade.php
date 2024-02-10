@extends('admin.layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Shipping Management</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('categories.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">

            <form action="" method="post" name="ShippingForm" id="ShippingForm">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <select name="country" id="country" class="form-control">
                                        <option value="">Select a Country</option>

                                        @if ($countries->isNotEmpty())
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                            <option value="rest_of_world">Rest of the world</option>
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <input type="text" name="amount" id="amount" class="form-control"
                                    placeholder="Amount">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>

                        </div>
                    </div>
                </div>

            </form>


        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('customJs')
    <script>
        // Web Validation
        $('#ShippingForm').submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);

            $.ajax({
                url: '{{ route('categories.store') }}',
                type: 'post',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);

                    if (response['status'] == true) {

                        window.location.href = "{{ route('categories.index') }}"

                        $('#name').addClass('is-invalid')
                            .siblings('p')
                            .removeClass('invalid-feedback').html('');

                        $('#slug').addClass('is-invalid')
                            .siblings('p')
                            .removeClass('invalid-feedback').html('');

                    } else {
                        var errors = response['errors'];
                        if (errors['name']) {
                            $('#name').addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback').html(errors['name']);
                        } else {
                            $('#name').addClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }

                        if (errors['slug']) {
                            $('#slug').addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback').html(errors['slug']);
                        } else {
                            $('#slug').addClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                    }



                },
                error: function(jqXHR, exception) {
                    console.log('Something went worng');
                }
            })
        });
    </script>
@endsection