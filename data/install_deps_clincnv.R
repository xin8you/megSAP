install.packages("optparse", repos="https://cran.rstudio.com")
install.packages("robustbase", repos="https://cran.rstudio.com")
install.packages("MASS", repos="https://cran.rstudio.com")
install.packages("data.table", repos="https://cran.rstudio.com")
install.packages("foreach", repos="https://cran.rstudio.com")
install.packages("doParallel", repos="https://cran.rstudio.com")
install.packages("mclust", repos="https://cran.rstudio.com")
install.packages("R.utils", repos="https://cran.rstudio.com")
install.packages("RColorBrewer", repos="https://cran.rstudio.com")

#Party is no longer available for R 3.4, which is installed on Ubuntu 18.04. So we install the old version manually
library("devtools")
install_version("mvtnorm", version = "1.0-8", repos = "http://cran.us.r-project.org")
install_version("coin", version = "1.0-8", repos = "http://cran.us.r-project.org")
install_version("party", version = "1.0-8", repos = "http://cran.us.r-project.org")