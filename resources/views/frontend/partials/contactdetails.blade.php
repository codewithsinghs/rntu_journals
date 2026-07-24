<!-- Heading -->

@if($contacts->contact_badge)
<div class="rntu-contact-heading">
    {{ $contacts->contact_badge }}
</div>
@endif

<!-- Contact Cards -->

<div class="rntu-contact-grid">

    <!-- Principal Contact -->

    <div class="rntu-contact-card">

        <h3>{{ $contacts->contact_heading1 }}</h3>

        <p>{!!$contacts->contact_detail1!!}</p>

    </div>

    <!-- Publisher Contact -->

    <div class="rntu-contact-card">

        <h3>{{ $contacts->contact_heading2 }}</h3>

        <p>{!! $contacts->contact_detail2 !!}</p>

    </div>

</div>


<!-- Editorial Office -->

<div class="rntu-contact-bottom">

    <div class="rntu-contact-card rntu-editorial-card">

        <h3>{{ $contacts->contact_heading3 }}</h3>

        <p>{!! $contacts->contact_detail3 !!}</p>

    </div>

</div>