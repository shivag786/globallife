<x-layouts.app title="Edit Testimonial" heading="Edit Testimonial">
    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
        @include('admin.testimonials._form')
    </form>
</x-layouts.app>
