<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>EoI</title>

    <style>
        @page {
			margin: 50px 50px 80px 50px;
        }

        body {
            font-size: 14px;
            color: #000;
        }

        th {
            vertical-align: top;
        }

        .padding-5 {
            padding: 5px;
        }

        .padding-8 {
            padding: 8px;
        }

        .form-label {
            font-weight: bold;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .pd-10 td,
        .pd-10 th {
            padding: 10px;
        }

        .pd-5 td,
        .pd-5 th {
            padding: 5px;
        }

        .border td,
        .border th {
            border: 1px solid #ccc;
        }

       .cover-page {
            width: 100%;
            height: auto;
            background-color: #95b3d7;
        }

        .cover-inner {
            width: 80%;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
        }

        .address-box {
            background: #1f487c;
            color: #fff;
            padding: 20px;
            height: 35%;
			vertical-align:bottom;
        }

        .expression-box {
            padding-top: 20px;
			height: 55%;
        }

        .heading-main {
            font-size: 30px;
            color: #4f80bb;
            line-height: 45px;
        }

        .project-title {
            font-size: 16px;
            font-weight: bold;
            line-height: 25px;
        }

        .no-border,
        .no-border td {
            border: none !important;
        }

table {
    width: 100%;
    border-collapse: collapse;
}

tr,
td {
    vertical-align: top;
}

        img {
            max-width: 100%;
        }

        p {
            margin: 0 0 8px;
        }

.section-table {
    width: 100%;
    margin-bottom: 10px;
	border-collapse: collapse;
	border: 1px solid #eee;
	outline: 1px solid #eee;
	outline-offset: -1px;
}
.section-table td,
.section-table th {
    word-wrap: break-word;
    overflow-wrap: break-word;
	border: 1px solid #eee;
	padding: 10px;
}

p {
    margin-top: 0;
}

ul, ol {
    margin-top: 5px;
    margin-bottom: 5px;
}		
.section-block {
    width: 100%;
    margin-bottom: 20px;
}

.section-heading {
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 14px;
}

.content-block {
    width: 100%;
}

.content-block p {
    margin: 0 0 10px;
    line-height: 1.5;
}

.content-block table {
    width: 100%;
    border-collapse: collapse;
}

.content-block table td,
.content-block table th {
    border: 1px solid #ccc;
    padding: 6px;
}
    </style>
</head>

<body>

    <!-- COVER PAGE -->

    <div class="cover-page">

        <table class="no-border" style="height:100%;">
            <tr>

                <td width="40%"></td>

                <td width="50%">

                    <div class="cover-inner">

                        <div class="address-box" style="position:relative;">
							<div style="position:absolute; bottom:5px;">
                            Chhattisgarh Infotech Promotion Society (CHiPS)<br>
                            State Data Centre Building,<br>
                            Near Police Control Room,<br>
                            Civil Lines, Raipur,<br>
                            Chhattisgarh – 492001<br><br>

                            Tel.: +91-771-4014158<br>
                            Email: ceochips@nic.in
							</div>
                        </div>

                        <div class="expression-box">

                            <div class="heading-main">
                                Expression of Interest (EoI)
                            </div>

                            <br><br>

                            <div class="project-title">
                                {{ $data->projecttitle }}
                            </div>

                        </div>

                    </div>

                </td>

                <td width="10%"></td>

            </tr>
        </table>

    </div>

    <div class="page-break"></div>

    @include('admin.pdfpages.page_indexing_table')

    @include('admin.pdfpages.factsheet_table')

    <div class="page-break"></div>

    @include('admin.pdfpages.objective_about_table')

	@if($tier_html!='')
	<div class="section-block">

		<div class="section-heading">
			4. Team Requirement
		</div>
		<div class="content-block">
			{!! $team_composition !!}
		</div>
		<div class="content-block">
			{!! $tier_html !!}
		</div>

	</div>	
	@endif

    @include('admin.pdfpages.other_table')

    @include('admin.pdfpages.evaluation_table')

    <br><br>

    <div style="text-align:center;">
        *** End of Document ***
    </div>

<script type="text/php">
    if (isset($pdf)) {

        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";

        $font = $fontMetrics->get_font("Helvetica", "normal");

        $size = 10;

        $width = $pdf->get_width()+50;
        $height = $pdf->get_height();

        $textWidth = $fontMetrics->get_text_width($text, $font, $size);

        // FIX: use absolute center but shift slightly left for Dompdf rendering offset
        $x = ($width - $textWidth) / 2;
        $y = $height - 35;

        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
@php

function cleanHtml($html)
{
    // Remove empty paragraphs
    $html = preg_replace('/<p>(&nbsp;|\s)*<\/p>/i', '', $html);
    $html = preg_replace('/<p><br\s*\/?><\/p>/i', '', $html);

    // Remove page-break CSS
    $html = preg_replace('/page-break-after\s*:\s*always/i', '', $html);

    // Remove all heading tags but keep their content
    $html = preg_replace('/<\/?h[1-6][^>]*>/i', '', $html);

    // Remove inline style attributes (recommended for PDF consistency)
    $html = preg_replace('/\sstyle=("|\').*?\1/i', '', $html);
	$html = preg_replace('/<\/?(strong|b)[^>]*>/i', '', $html);
	$html = '<div style="font-weight:normal">' . $html . '</div>';

    return $html;
}
@endphp
</body>

</html>