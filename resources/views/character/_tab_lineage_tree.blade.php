<div class="container text-center character-lineage-tree">
    <div class="row">
        <div class="col">
            <div class="border-bottom mb-1 generation-1">
                <span class="font-weight-bold">Reosean 1</span><br>{!! $line['sire'] !!}
            </div>
            <div class="row">
                <div class="col">
                    <div class="border-bottom mb-1 generation-2">
                        <abbr class="font-weight-bold" title="Grandparent 1">GR1</abbr><br>{!! $line['sire_sire'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 1">GGR1</abbr><br>{!! $line['sire_sire_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 2">GGR2</abbr><br>{!! $line['sire_sire_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="border-bottom mb-1 generation-2">
                        <abbr class="font-weight-bold" title="Grandparent 2">GR2</abbr><br>{!! $line['sire_dam'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 3">GGR3</abbr><br>{!! $line['sire_dam_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 4">GGR4</abbr><br>{!! $line['sire_dam_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="border-bottom mb-1 generation-1">
                <span class="font-weight-bold">Reosean 2</span><br>{!! $line['dam'] !!}
            </div>
            <div class="row">
                <div class="col">
                    <div class="border-bottom mb-1 generation-2">
                        <abbr class="font-weight-bold" title="Grandparent 3">GR3</abbr><br>{!! $line['dam_sire'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 5">GGR5</abbr><br>{!! $line['dam_sire_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 6">GGR6</abbr><br>{!! $line['dam_sire_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="border-bottom mb-1 generation-2">
                        <abbr class="font-weight-bold" title="Grandparent 4">GR4</abbr><br>{!! $line['dam_dam'] !!}
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 7">GGR7</abbr><br>{!! $line['dam_dam_sire'] !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1 generation-3">
                                <abbr title="Great Grandparent 8">GGR8</abbr><br>{!! $line['dam_dam_dam'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .character-lineage-tree {
	--width:53%;
    }
    .character-lineage-tree div[class*=generation] {
        position: relative;
        padding: 10px 0;
    }
    .character-lineage-tree div[class*=generation]::after {
        content: "";
        position: relative;
        margin-top: 10px;
        display: block;
        width: var(--width);
        height: 2px;
        background-color: var(--gray-700);
        left: calc(50% - calc(var(--width) /2));
    }
    .character-lineage-tree div[class*=generation]::before {
        content: "";
        position: absolute;
        display: block;
        width: 2px;
        height: 20px;
        background-color: var(--gray-700);
        top: -15px;
        left: calc(100% / 2);
    }
    .character-lineage-tree .generation-1::before,
    .character-lineage-tree .generation-3::after {
        content: unset !important;
    }
</style>