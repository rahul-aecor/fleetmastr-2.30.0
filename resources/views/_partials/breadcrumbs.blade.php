@if ($breadcrumbs)
<?php
$breadcrumbsLastIndex=count($breadcrumbs)>1?count($breadcrumbs)-1:1;
?>
	<ul class="page-breadcrumb">
		<li>
			@if(count($breadcrumbs)>1 && isset($breadcrumbs[$breadcrumbsLastIndex]) && isset($breadcrumbs[$breadcrumbsLastIndex]->overwriteFirstLink))
			<?php
				$overwriteFirstLink=$breadcrumbs[$breadcrumbsLastIndex]->overwriteFirstLink;
			?>
				@if(isset($overwriteFirstLink['class']))
					<i class="{{$overwriteFirstLink['class']}}"></i>
				@else
					<i class="jv-icon jv-home"></i>
				@endif
				@if(isset($overwriteFirstLink['url']))
				<a href="{{$overwriteFirstLink['url']}}">{{$overwriteFirstLink['title']}}</a>
				@else
				<a href="/">Home</a>
				@endif
			@else
			<i class="jv-icon jv-home"></i>
			<a href="/">Home</a>
			@endif
			<i class="fa fa-angle-right"></i>
		</li>
		@foreach ($breadcrumbs as $breadcrumb)
			@if ($breadcrumb->url && !$breadcrumb->last)
				<li><a href="{{{ $breadcrumb->url }}}">{{{ $breadcrumb->title }}}</a><i class="fa fa-angle-right"></i></li>
			@else
				<li class="active">{{{ $breadcrumb->title }}}</li>
			@endif
		@endforeach
	</ul>
@endif
