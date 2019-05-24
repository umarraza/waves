<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Local Resources</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

	<div class="container">
		<center>
			<p style="font-size: 45px !important">Local Rescources</p>
		</center>
		<br>
		<p><b>School Name: </b>{{ $schoolProfile->schoolName }}</p>
		<p><b>Generated By: </b>{{$userName}}</p>
		<p><b>Report Generated at: </b>{{$reportDate}}</p>
		<hr>
		@if(count($localResource)>0)
			@foreach($localResource as $res)
			<div>
				{{-- <p><b><u>Name:</u> </b>{{$res->name}}</p>
				<p><b><u>Insurance Type:</u> </b>{{$res->insuranceType}}</p>
				<p><b><u>Address:</u> </b>{{$res->streetAddress}} {{$res->city}} {{$res->state}}</p>
				<p><b><u>Zip Code:</u> </b>{{$res->zipCode}}</p>
				<p><b><u>Phone Number:</u> </b>{{$res->phoneNumber}}</p>
				<p><b><u>Website:</u> </b>{{$res->website}}</p>
				<p><b><u>Service:</u> </b>{{$res->serviceTypeId}}</p>
				 --}}

				 <p><b>Name: </b>{{$res->name}}</p>
				<p><b>Insurance Type: </b>{{$res->insuranceType}}</p>
				<p><b>Address: </b>{{$res->streetAddress}} {{$res->city}} {{$res->state}}</p>
				<p><b>Zip Code: </b>{{$res->zipCode}}</p>
				<p><b>Phone Number: </b>{{$res->phoneNumber}}</p>
				<p><b>Website: </b>{{$res->website}}</p>
				<p><b>Service: </b>{{$res->serviceTypeId}}</p>
			</div>
			<hr>
			@endforeach
		@else
			<div>
				<center>
					No Data Found
				</center>
			</div>
		@endif
	</div>
	
</body>
</html>