<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>{{ __('loginform.vendorwindowtitle') }}</title>
    <link rel="shortcut icon" href="{{ asset('panel/assets/images/basic/screen.png') }}">
    <meta name="description" content="User login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <link rel="stylesheet" href="{{ asset('panel/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('panel/assets/font-awesome/4.5.0/css/font-awesome.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('panel/assets/css/fonts.googleapis.com.css') }}" />

    <link rel="stylesheet" href="{{ asset('panel/assets/css/jquery-ui.custom.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('panel/assets/css/jquery.gritter.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('panel/assets/css/ace.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-part2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-rtl.min.css') }}" />
    <script src="{{ asset('panel/assets/js/forwarder.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('panel/assets/css/ace-ie.min.css') }}" />

    <script src="{{ asset('panel/assets/js/html5shiv.min.js') }}"></script>
    <script src="{{ asset('panel/assets/js/respond.min.js') }}"></script>
</head>

    <body class="login-layout light-login">
<form name="lng" id="lng">
<select id="language-selector" onchange="selectLanguage()" style="float:right;">
    <option value="en" {{ session('locale') == 'en' ? 'selected' : '' }}>ENGLISH</option>
    <option value="mr" {{ session('locale') == 'mr' ? 'selected' : '' }}>MARATHI</option>
    <option value="hn" {{ session('locale') == 'hn' ? 'selected' : '' }}>HINDI</option>
</select>
</form>
        <div class="main-container">
            <div class="main-content">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="login-container">
                            <div class="center">
                                <h4 class="blue" id="id-company-text">{{ __('common.companyname') }}<br><br>{{ __('loginform.vendorloginwindow') }}</h4>                             
                            </div>

                            <div class="space-6"></div>

                            <div class="position-relative">
                                <div id="login-box" class="login-box visible widget-box no-border">
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <h4 class="header blue lighter bigger">
                                                <i class="ace-icon fa fa-user blue"></i>
                                                {{ __('loginform.enterlogindetail') }}
                                            </h4>

                                            <div class="space-6"></div>
<form method="POST" action="{{ route('login-vendor') }}">
    @if(Session::has('success'))
        <div class="alert alert-success">{{ Session::get('success') }}</div>
    @endif
    @if(Session::has('fail'))
        <div class="alert alert-danger"><i class="fa fa-warning"></i> {{ Session::get('fail') }}</div>
    @endif
    @csrf
    
    <fieldset>
        <label class="block clearfix">
            <span class="block input-icon input-icon-right">
                <input type="text" name="loginid" :value="old('loginid')" id="loginid" class="form-control" placeholder="Username / Registered Email" required />                
                <i class="ace-icon fa fa-user"></i>
            </span>
            <span class="text-danger">@error('loginid') {{ $message }} @enderror</span>
        </label>

        <label class="block clearfix">
            <span class="block input-icon input-icon-right">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required/>
                <i class="ace-icon fa fa-lock"></i>
            </span>
            <span class="text-danger">@error('password') {{ $message }} @enderror</span>
        </label>

        <div class="space"></div>

        <div class="clearfix">

            <button type="submit" class="width-35 pull-right btn btn-sm btn-primary">
                <i class="ace-icon fa fa-key"></i>
                <span class="bigger-110">{{ __('loginform.loginbtn') }}</span>
            </button>
        </div>

        <div class="space-4"></div>
    </fieldset>
</form>
                                            <div class="space-6"></div>

                                        </div><!-- /.widget-main -->

                                        <div class="toolbar clearfix">
                                            <div>
                                                <a href="#" data-target="#forgot-box" class="forgot-password-link">
                                                    <i class="ace-icon fa fa-arrow-left"></i>
                                                    {{ __('loginform.forgotlink') }}
                                                </a>
                                            </div>

                                        </div>
                                    </div><!-- /.widget-body -->
                                </div><!-- /.login-box -->
								<div id="forgot-box" class="forgot-box widget-box no-border">
									<div class="widget-body">
										<div class="widget-main">
											<h4 class="header red lighter bigger">
												<i class="ace-icon fa fa-key"></i>
												{{ __('loginform.retrive') }}
											</h4>

											<div class="space-6"></div>
											<p>
												{{ __('loginform.emaillabel') }}
											</p>

											<form name="forgot" id="forgot" action="#" method="post">
												<input type="hidden" name="acn" id="acn" value="forgot">											
												<fieldset>
													<label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="email" class="form-control" placeholder="Email" />
															<i class="ace-icon fa fa-envelope"></i>
														</span>
													</label>

													<div class="clearfix">
														<button type="button" class="width-35 pull-right btn btn-sm btn-danger">
															<i class="ace-icon fa fa-envelope"></i>
															<span class="bigger-110">{{ __('loginform.sendmail') }}</span>
														</button>
													</div>
												</fieldset>
											</form>
										</div><!-- /.widget-main -->

										<div class="toolbar center">
											<a href="#" data-target="#login-box" class="back-to-login-link">
												{{ __('loginform.backtologin') }}
												<i class="ace-icon fa fa-arrow-right"></i>
											</a><br>
										</div>
									</div><!-- /.widget-body -->
								</div><!-- /.forgot-box -->


                                <!-- /.signup-box -->
                            </div><!-- /.position-relative -->

                        </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.main-content -->
        </div><!-- /.main-container -->

        <!-- basic scripts -->

        <script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
        <script src="{{ asset('panel/assets/js/jquery-1.11.3.min.js') }}"></script>
        <script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>        
		<script>
					jQuery(function($) {
			 $(document).on('click', '.toolbar a[data-target]', function(e) {
				e.preventDefault();
				var target = $(this).data('target');
				$('.widget-box.visible').removeClass('visible');//hide others
				$(target).addClass('visible');//show target
			 });
			});
			function selectLanguage()
			{
				var locale = document.getElementById("language-selector").value;
				window.location.href = "{{ url('lang') }}/" + locale;
			}
		</script>
    </body>
</html>
<script src="{{ ('panel/assets/js/bootstrap.min.js') }}"></script>