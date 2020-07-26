@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Breadcrumb --}}
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('restaurant.index')}}">Restaurant</a></li>
            <li class="breadcrumb-item active">{{$restaurant->name}}</li>
        </ol>
    </nav>

    <div class="row mb-2"> {{-- Basic Info --}}
        <div class="col-md-4 mb-3">
            <img src="{{asset('storage/images/restaurant/' . $restaurant->image_path)}}" onerror="this.onerror=null; this.src='{{asset('storage/images/restaurant/placeholder.png')}}'" class="d-block m-auto m-md-0 img-fluid dingg-border rounded" alt="{{$restaurant->name}} picture">
        </div>
        <div class="col-md-8 text-center text-md-left">
            <h1 class="d-inline-block mr-2 mb-2">{{$restaurant->name}}</h1> {{-- Title --}}
            @include('includes.restaurant.edit-delete-button')              {{-- Edit / Delete --}}
            <p class="lead mb-2">{{$restaurant->description}}</p>           {{-- Description --}}


            <div class="d-inline-block"> {{-- Categories --}}
                @foreach ($restaurant->categories()->get() as $category)
                    <a href="{{route('category.show', $category->id)}}" class="btn btn-dark btn-secondary active mb-2" style="vertical-align: super;" role="button">{{$category->name}}</a>
                @endforeach
            </div>

            <div class="d-inline-block mb-3"> {{-- Rating / Favorite --}}
                <button class="btn btn-danger font-weight-bold disabled" style="opacity: 1; vertical-align: super;">{{'⭐  4.6'}} / 5</button>
                <form class="d-inline-block">
                    <button type="submit" style="vertical-align: super;" class="btn btn-outline-success">Favorite ❤️</button>
                </form>
            </div>

            <table class="table table-sm"> {{-- Info --}}
                <tbody>
                    <tr>
                        <th scope="row">Address</th>
                        <td>{{$restaurant->address}}</td>
                    </tr>
                    <tr>
                        <th scope="row">Phone</th>
                        <td><a href="tel:{{$restaurant->phone}}">{{$restaurant->phone}}</a></td>
                    </tr>
                    <tr>
                        <th scope="row">Website</th>
                        <td><a href="{{$restaurant->website}}">{{$restaurant->website}}</></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Order button --}}
    <div class="px-5">
        <a href="#" class="btn btn-light text-dark btn-block dingg-border btn-lg">Order 🍑🍆</a>
    </div>



</div>
@include('includes.category.delete-modal')
@endsection
