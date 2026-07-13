<x-layouts.app title="Add Testimonial" heading="Add Testimonial">
    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
        @include('admin.testimonials._form')
    </form>
</x-layouts.app>
