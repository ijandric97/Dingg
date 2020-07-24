@can('is-admin', Auth::user())
    <div class="btn-group" role="group" style="vertical-align: super;">
        <a class="btn btn-success" href="{{route('restaurant.create')}}" role="button">+ Create</a>
        <a class="btn btn-primary" href="{{route('restaurant.index')}}" role="button">View All ↗</a>
    </div>
@else
    <a class="btn btn-primary" style="vertical-align: super;" href="{{route('restaurant.index')}}" role="button">View all ↗</a>
@endcan
