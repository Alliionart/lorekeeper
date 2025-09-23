<div class="container text-center">
<div class="row">
        <div class="col">
            <div class="border-bottom mb-1">
                <span class="font-weight-bold">Sire</span><br>{!! $line['sire'] !!}
            </div>
            <div class="row">
                <div class="col">
                    <div class="border-bottom mb-1">
                        <abbr class="font-weight-bold" title="Sire's Sire">SS</abbr><br>{!! $line['sire_sire'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Sire's Sire's Sire">SSS</abbr><br>{!! $line['sire_sire_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Sire's Sire's Dam">SSD</abbr><br>{!! $line['sire_sire_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="border-bottom mb-1">
                        <abbr class="font-weight-bold" title="Sire's Dam">SD</abbr><br>{!! $line['sire_dam'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Sire's Dam's Sire">SDS</abbr><br>{!! $line['sire_dam_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Sire's Dam's Dam">SDD</abbr><br>{!! $line['sire_dam_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="border-bottom mb-1">
                <span class="font-weight-bold">Dam</span><br>{!! $line['dam'] !!}
            </div>
            <div class="row">
                <div class="col">
                    <div class="border-bottom mb-1">
                        <abbr class="font-weight-bold" title="Dam's Sire">DS</abbr><br>{!! $line['dam_sire'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Dam's Sire's Sire">DSS</abbr><br>{!! $line['dam_sire_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Dam's Sire's Dam">DSD</abbr><br>{!! $line['dam_sire_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="border-bottom mb-1">
                        <abbr class="font-weight-bold" title="Dam's Dam">DD</abbr><br>{!! $line['dam_dam'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Dam's Dam's Sire">DDS</abbr><br>{!! $line['dam_dam_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <abbr title="Dam's Dam's Dam">DDD</abbr><br>{!! $line['dam_dam_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<div id="lineage"><span class="label">{{ $character->fullName }}</span>
  <div class="branch lv1">
    <div class="entry"><span class="label"><strong>Reosean 1</strong> <br>{!! $line['sire'] !!}</span>
      <div class="branch lv2">
        <div class="entry"><span class="label"><strong>GR1</strong> <br>{!! $line['sire_sire'] !!}</span>
          <div class="branch lv2">
            <div class="entry"><span class="label out"><strong>GGR1</strong> <br>{!! $line['sire_sire_sire'] !!}</span></div>
            <div class="entry"><span class="label out"><strong>GGR2</strong> <br>{!! $line['sire_sire_dam'] !!}</span></div>
          </div>
        </div>
        <div class="entry"><span class="label"><strong>GR2</strong> <br>{!! $line['sire_dam'] !!}</span>
          <div class="branch lv2">
            <div class="entry"><span class="label out"><strong>GGR3</strong> <br>{!! $line['sire_dam_sire'] !!}</span></div>
            <div class="entry"><span class="label out"><strong>GGR4</strong> <br>{!! $line['sire_dam_dam'] !!}</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="entry"><span class="label"><strong>Reosean 2</strong> <br>{!! $line['dam'] !!}</span>
      <div class="branch lv2">
        <div class="entry"><span class="label"><strong>GR3</strong> <br>{!! $line['dam_sire'] !!}</span>
          <div class="branch lv2">
            <div class="entry"><span class="label out"><strong>GGR5</strong> <br>{!! $line['dam_sire_sire'] !!}</span></div>
            <div class="entry"><span class="label out"><strong>GGR6</strong> <br>{!! $line['dam_sire_dam'] !!}</span></div>
          </div>
        </div>
        <div class="entry"><span class="label"><strong>GR4</strong> <br>{!! $line['dam_dam'] !!}</span>
          <div class="branch lv2">
            <div class="entry"><span class="label out"><strong>GGR7</strong> <br>{!! $line['dam_dam_sire'] !!}</span></div>
            <div class="entry"><span class="label out"><strong>GGR8</strong> <br>{!! $line['dam_dam_dam'] !!}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    

</div>
