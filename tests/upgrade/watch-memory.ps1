param([Parameter(Mandatory=$true)][string]$ContainerName)
$ErrorActionPreference='Stop'
if($ContainerName -notmatch '^adsr-[a-z0-9-]+$'){throw 'Only task containers are supported'}
$root=Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$log=Join-Path $root ('tests\baseline\artifacts\'+$ContainerName+'-memory.csv')
$seen=$false;$deadline=[DateTime]::UtcNow.AddMinutes(5)
while($true){
 $names=@(docker ps --format '{{.Names}}');if($LASTEXITCODE -ne 0){exit 3}
 if($names -contains $ContainerName){$seen=$true}else{if($seen -or [DateTime]::UtcNow -gt $deadline){exit 0}}
 if($names -contains $ContainerName){
  try{$o=Get-CimInstance Win32_OperatingSystem;$used=[math]::Round(100*(1-$o.FreePhysicalMemory/$o.TotalVisibleMemorySize),1)}catch{docker stop --timeout 10 $ContainerName;exit 2}
  Add-Content $log ((Get-Date -Format o)+','+$used+','+$names.Count)
  if($used -ge 85 -or $names.Count -gt 1){docker stop --timeout 10 $ContainerName;exit 2}
 }
 Start-Sleep -Seconds 10
}
