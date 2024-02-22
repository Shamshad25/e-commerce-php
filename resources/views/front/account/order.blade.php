@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('account.profile') }}">My Account</a></li>
                    <li class="breadcrumb-item">My Orders</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-11 ">
        <div class="container  mt-5">
            <div class="row">
                <div class="col-md-3">
                    @include('front.account.common.sidebar')

                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h5 mb-0 pt-2 pb-2">My Orders</h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Orders #</th>
                                            <th>Date Purchased</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="order-detail.php">OR756374</a>
                                            </td>
                                            <td>11 Nav, 2022</td>
                                            <td>
                                                <span class="badge bg-success">Delivered</span>

                                            </td>
                                            <td>$400</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="order-detail.php">OR756374</a>
                                            </td>
                                            <td>10 Oct, 2022</td>
                                            <td>
                                                <span class="badge bg-success">Delivered</span>

                                            </td>
                                            <td>$400</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="order-detail.php">OR756374</a>
                                            </td>
                                            <td>02 Sep, 2022</td>
                                            <td>
                                                <span class="badge bg-success">Delivered</span>

                                            </td>
                                            <td>$400</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="order-detail.php">OR756374</a>
                                            </td>
                                            <td>01 Dec, 2022</td>
                                            <td>
                                                <span class="badge bg-success">Delivered</span>

                                            </td>
                                            <td>$400</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
