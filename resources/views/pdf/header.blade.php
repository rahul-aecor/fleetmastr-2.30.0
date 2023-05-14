<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script>
        function substitutePdfVariables() {

            function getParameterByName(name) {
                var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
                return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
            }

            function substitute(name) {
                var value = getParameterByName(name);
                var elements = document.getElementsByClassName(name);

                for (var i = 0; elements && i < elements.length; i++) {
                    elements[i].textContent = value;
                }
            }

            ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection']
                .forEach(function(param) {
                    substitute(param);
                });
        }
    </script>
    <style type="text/css">
        #headerDiv span{
            font-family: 'Open Sans';
            font-size: 9px;

        }
    </style>
</head>
<body onload="substitutePdfVariables()">
    <div id="headerDiv" class="header-div">
        <!-- <span style="width:200px;padding-right:225px;">{{ $date->format('H:i:s d M Y') }}</span>
        <span style="width:200px;padding-right:225px;"><strong>fleet</strong>mastr</span>
        <span style="width:200px;">Page <span class="page"></span> of <span class="topage"></span></span> -->
        <table width="100%">
            <tr>
                <td style="font-size: 11px;">{{ $date->format('H:i:s d M Y') }}</td>
                <td style="font-size: 11px;"><strong>fleet</strong>mastr</td>
                <td style="font-size: 11px;" align="right">Page <span class="page"></span> of <span class="topage"></span></td>
            </tr>
        </table>
    </div>    
</body>
</html>