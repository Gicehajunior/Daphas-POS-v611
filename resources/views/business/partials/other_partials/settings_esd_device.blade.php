<div class="row"> 
        <div class="col-sm-12">
            <h4>@lang('ESD Device Settings'):</h4>
            <p>@lang('Configure ESD device connectivity settings')</p>
            <br/>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('esd_api_bridger_endpoint', __('ESD API Bridging Script Endpoint') . ':') !!}
                {!! Form::text('pos_settings[esd_api_bridger_endpoint]', isset($pos_settings['esd_api_bridger_endpoint']) ? $pos_settings['esd_api_bridger_endpoint'] : null, ['class' => 'form-control', 'id' => 'esd_api_bridger_endpoint']); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('esd_device_endpoint', __('ESD Device Endpoint') . ':') !!}
                {!! Form::text('pos_settings[esd_device_endpoint]', isset($pos_settings['esd_device_endpoint']) ? $pos_settings['esd_device_endpoint'] : null, ['class' => 'form-control', 'id' => 'esd_device_endpoint']); !!}
            </div>
        </div>  

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('esd_aclas_api_bridger', __('ESD Aclas API Bridger') . ':') !!} 
                <a name="" id="" class="btn btn-primary" href="{{ action([\App\Http\Controllers\Custom\BusinessCustomController::class, 'downloadESDAClassApiBridgerScriptLibrary'])}}" download="Aclas-API-ESD-Bridger.php" role="button"><i class="fas fa-file-download"></i> Download Aclas API Bridger Library</a> 
                <!-- Button trigger modal -->
                <button style="text-decoration-line: none;" type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#downloadTooltipTextModelId">
                    Installation Instructions
                </button> 
            </div>
        </div> 

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <br>
                    <label>
                        {!! Form::checkbox('pos_settings[enable_live_mode]', 1, !empty($pos_settings['enable_live_mode']), ['class' => 'input-icheck']); !!}
                        {{ __('custom.enable_live_mode') }}
                    </label>
                </div>
            </div>
        </div>
    </div>  


    {{-- Modals --}} 
    <div class="modal fade" id="downloadTooltipTextModelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ESD Connection, Aclas API Bridger Library Installation Instructions:</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="content">
                        <?php
                            $downloadTooltipText = nl2br("Installation Instructions:
                                1. Download the ESD Bridger File:
                                - Click on the following link to download the ESD Bridger file: [Download ESD Bridger](path/to/yourfile)
                                - Save the downloaded file to a location on your computer.

                                2. Move the ESD Bridger File to the XAMPP `htdocs` Directory:
                                - Locate the downloaded ESD Bridger file.
                                - Move the file to the `htdocs` directory of your XAMPP installation.

                                3. Final Path:
                                - The final path to the ESD Bridger file should be similar to: `xampp/htdocs/yourfile`

                                4. Purpose of the ESD Bridger File:
                                - The ESD Bridger serves as a communication bridge between your application and the Actual ESD Device API. Ensure that the configuration and integration with your application are appropriately set up to establish seamless communication with the ESD device.

                                Now, the ESD Bridger is ready for use.");
                        ?>
                        <?= $downloadTooltipText ?>
                    </div>
                </div> 
            </div>
        </div>
    </div>