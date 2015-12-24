{assign var='controllerName' value=$smarty.get.controller}
<div class="jumbotron jumbotronLengow">
    <h1>Hello, world ! Get started !</h1>

    <p><a class="btn btn-primary btn-lg" href="http://www.lengow.com/" role="button">See how lengow works</a></p>
</div>
<div class="jumbotron jumbotronLengow">
    <div class="container">
        <p>You are 2 steps away from launch</p>

        <div class="row rowLengow">
            <div class="col-sm-6 col-md-6 jumbotronLengow">
                <div class="thumbnail">
                    <div class="caption">
                        <h3 >Setup your carrier</h3>

                        <p class="descLengow">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit
                            non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id
                            elit.</p>

                        <p><a href="
                        {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
                        {$link->getAdminLink('AdminLengowConfig')}
                        {else}
                        #
                        {/if}
                        " class="btn btn-primary " role="button">Let's go ! <i class="icon-rocket"></i></a></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6 jumbotronLengow">
                <div class="thumbnail">
                    <div class="caption">
                        <h3>Import your orders</h3>

                        <p class="descLengow">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit
                            non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id
                            elit.</p>

                        <p><a href="
                        {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
                        {$link->getAdminLink('AdminLengowConfig')}
                        {else}
                        #
                        {/if}
                        " class="btn btn-primary" role="button">Let's go ! <i class="icon-rocket"></i></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>